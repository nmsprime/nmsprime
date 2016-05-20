<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use File;
use DB;

use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\AccountingRecord;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\BillingLogger;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\Salesman;

class accountingCommand extends Command {

	/**
	 * The console command & table name, description, data arrays
	 *
	 * @var string
	 */
	protected $name 		= 'billing:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';
	protected $dir 			= 'data/billing/'; 				// relative to storage/app/ - Note: completed by month in constructor!
	
	protected $dates;					// offen needed time strings for faster access - see constructor


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->dates = array(

			'today' 		=> date('Y-m-d'),
			'm' 			=> date('m'),
			'Y' 			=> date('Y'),

			'this_m'	 	=> date('Y-m'),
			'thism_01'		=> date('Y-m-01'),
			'thism_bill'	=> date('m/Y'),

			'lastm'			=> date('m', strtotime("first day of last month")),			// written this way because of known bug
			'lastm_01' 		=> date('Y-m-01', strtotime("first day of last month")),
			'lastm_bill'	=> date('m/Y', strtotime("first day of last month")),
			'lastm_Y'		=> date('Y-m', strtotime("first day of last month")),		// strtotime(first day of last month) is integer with actual timestamp!

			'nextm_01' 		=> date('Y-m-01', strtotime("+1 month")),

			'null' 			=> '0000-00-00',
			'm_in_sec' 		=> 60*60*24*30,			// month in seconds
			'last_run'		=> '0000-00-00', 					// filled on start of execution

		);

		$this->dir .= date('Y_m').'/';

		parent::__construct();

	}



	/**
	 * Execute the console command: Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
	 *
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function fire()
	{
		// only while testing!! - TODO: remove for production system
		DB::table('item')->update(['payed' => false]);

		$logger = new BillingLogger;
		$logger->addInfo(' #####    Start Accounting Command    #####');

		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();
		$contracts  = Contract::with('items', 'items.product', 'costcenter')->get();		// eager loading for better performance
		$salesmen 	= Salesman::all();


		// remove all entries of this month permanently (if already created)
		$ret = AccountingRecord::whereBetween('created_at', [$this->dates['thism_01'], $this->dates['nextm_01']])->forceDelete();
		if ($ret)
			$logger->addNotice('Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');

		// set time of last run
		$last_run = AccountingRecord::select('created_at')->orderBy('id', 'desc')->first();
		if ($last_run)
			$this->dates['last_run'] = $last_run->created_at;

		// init product types of salesmen and invoice nr counters for each sepa account
		$this->_init($sepa_accs, $salesmen, $conf);

		
		/*
		 * Loop over all Contracts
		 */
		foreach ($contracts as $c)
		{
			// debugging output
			var_dump($c->id); //, round(microtime(true) - $start, 4));
			// dd(date('Y-m-d', strtotime('next day')));
			// dd(strtotime(date('2016-04-01')), strtotime(date('2016-03-31 23:59:59')));
			// dd(strtotime('0000-00-00'), strtotime(null), date('Y-m-d', strtotime('last year')));
			// dd(date('z', strtotime(date('Y-12-31'))), date('Y-m-d', strtotime('last month')), date('L'), date('Y-m-d', strtotime('first day of last month')));


			// Skip invalid contracts
			if (!$c->check_validity())
			{
				$logger->addNotice('Contract '.$c->number.' has no valid dates for this month', [$c->id]);
				continue;				
			}

			if (!$c->create_invoice)
			{
				$logger->addInfo('Create invoice for Contract '.$c->number.' is off', [$c->id]);
				continue;
			}

			$charge 	= []; 					// total costs for this month for current contract
			$c->expires = date('Y-m-01', strtotime($c->contract_end)) == $this->dates['lastm_01'];


			/*
			 * Collect item specific data for all billing files
			 */
			foreach ($c->items as $item)
			{
				// skip invalid items
				if (!$item->check_validity($item->get_billing_cycle() == 'Yearly' ? 'year' : 'month'))
					continue;

				// skip if price is 0
				if (!($ret = $item->calculate_price_and_span($this->dates)))
					continue;

				// get account via costcenter
				$costcenter = $item->get_costcenter();
				$acc = $sepa_accs->find($costcenter->sepa_account_id);

				// increase invoice nr of sepa account, increase charge for account by price, calculate tax
				if (isset($c->charge[$acc->id]))
				{
					$c->charge[$acc->id]['net'] += $item->charge;
					$c->charge[$acc->id]['tax'] += $item->product->tax ? $item->charge * $conf->tax/100 : 0;
				}
				else
				{
					$c->charge[$acc->id]['net'] = $item->charge;
					$c->charge[$acc->id]['tax'] = $item->product->tax ? $item->charge * $conf->tax/100 : 0;
					$acc->invoice_nr += 1;
				}

				$item->charge = round($item->charge, 2);

				// save to accounting table (as backup for future) - NOTE: invoice nr counters are set initially from that table
				$rec = new AccountingRecord;
				$rec->store($item, $acc);

				// add item to accounting records of account, invoice and salesman
				$acc->add_accounting_record($item);
				$acc->add_invoice_item($item, $conf);
				if ($c->salesman_id)
					$salesmen->find($c->salesman_id)->add_item($item);

			} // end of item loop

			// get actual valid sepa mandate
			$mandate = $c->get_valid_mandate();

			if (!$mandate)
				$logger->addNotice('Contract '.$c->number.' has no valid sepa mandate', [$c->id]);


			// Add contract specific data for billing files
			foreach ($c->charge as $acc_id => $value)
			{
				$value['net'] = round($value['net'], 2);
				$value['tax'] = round($value['tax'], 2);

				$acc = $sepa_accs->find($acc_id);
				$acc->add_booking_record($c, $mandate, $value, $conf);
				$acc->add_invoice_data($c, $mandate, $value);

				// make bill already
				$acc['invoices'][$c->id]->make_invoice();

				// skip sepa part if contract has no valid mandate
				if (!$mandate)
					continue;

				$acc->add_sepa_transfer($mandate, $value['net'] + $value['tax'], $this->dates);
			}

		} // end of loop over contracts

		
		$this->_make_billing_files($sepa_accs, $salesmen);

	}



	/*
	 * Initialise models for this billing cycle
	 */
	private function _init($sepa_accs, $salesmen, $conf)
	{
		// init salesmen here because of performance (only 1 DB query)
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $key => $sm)
		{
			$sm->all_prod_types = $prod_types;
			// directory to save file
			$sm->dir = $this->dir;
		}

		foreach ($sepa_accs as $acc)
			$acc->dir = $this->dir;


		// actual invoice nr counters
		$last_run = AccountingRecord::orderBy('created_at', 'desc')->select('created_at')->first();
		if (is_object($last_run))
		{
			foreach ($sepa_accs as $acc)
			{
				// restart counter every year
				if ($this->dates['m'] == '01')
				{
					if ($conf->invoice_nr_start)
						$acc->invoice_nr = $conf->invoice_nr_start;
					continue;
				}

				$nr = AccountingRecord::where('sepa_account_id', '=', $acc->id)->orderBy('invoice_nr', 'desc')->select('invoice_nr')->first();
				if (is_object($nr))
					$acc->invoice_nr = $nr->invoice_nr;
			}
		}
		// first run for this system
		else
		{
			foreach ($sepa_accs as $acc)
			{
				if ($conf->invoice_nr_start)
					$acc->invoice_nr = $conf->invoice_nr_start;
			}
		}
	}


	/*
	 * stores all billing files besides invoices in the directory defined as property of this class
	 */
	private function _make_billing_files($sepa_accs, $salesmen)
	{
		foreach ($sepa_accs as $acc)
			$acc->make_billing_files();

		$salesmen[0]->prepare_output_file();
		foreach ($salesmen as $sm)
			$sm->print_commission();
	}


	/**
	 * Get the console command arguments / options
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			// ['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
		];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}



/*
 * Programming Notes
 */

// $logger = new Logger('Billing');
// $logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);

// switch ($this->argument('cycle'))
// {
// 	case 1:
// $logger->addInfo('Cycle without TV items/products');