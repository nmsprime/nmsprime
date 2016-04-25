<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use File;
use DB;

use Modules\ProvBase\Entities\Contract;
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
	protected $name 		= 'nms:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';
	protected $dir 			= '/var/www/data/billing/';
	
	protected $logger;					// billing logger instance
	protected $dates;					// offen needed time strings for faster access - see constructor


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// instantiate logger for billing
		$this->logger = new BillingLogger;

		$this->dates = array(
			'today' 		=> date('Y-m-d'),
			'm' 			=> date('m'),
			'Y' 			=> date('Y'),
			'this_m'	 	=> date('Y-m'),
			'this_m_bill'	=> date('m/Y'),
			'last_m'		=> date('m', strtotime("first day of last month")),			// written this way because of known bug
			'last_m_Y'		=> date('Y-m', strtotime("first day of last month")),
			'last_m_bill'	=> date('m/Y', strtotime("first day of last month")),
			'null' 			=> '0000-00-00',
			'lastm_01' 		=> date('Y-m-01', strtotime("first day of last month")),
			'thism_01'		=> date('Y-m-01'),
			'nextm_01' 		=> date('Y-m-01', strtotime("+1 month")),
			'last_run' 		=> '',					// important for price calculation!!
			'm_in_sec' 		=> 60*60*24*30,			// month in seconds
		);

		parent::__construct();
	}



	/**
	 * Execute the console command: Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
	 *
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function fire()
	{
		$this->logger->addInfo(' #####    Start Accounting Command    #####');

		// remove all entries of this month from accounting table if entries were already created (and create them new)
		$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this->dates['thism_01'])->where('created_at', '<=', $this->dates['nextm_01'])->first();
		if (is_object($actually_created))
		{
			$this->logger->addNotice('Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');
			DB::update('DELETE FROM '.$this->tablename.' WHERE created_at>='.$this->dates['thism_01']);
		}


		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();
		$salesmen 	= Salesman::all();
		$contracts  = Contract::with('items', 'items.product', 'costcenter')->get();		// eager loading for better performance

		// init salesmen here because of performance (only 1 DB query)
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $sm)
			$sm->all_prod_types = $prod_types;


		/*
		 * Initialise date of last run and actual invoice nr counters
		 */
		$last_run = DB::table($this->tablename)->orderBy('created_at', 'desc')->select('created_at')->first();
		if (is_object($last_run))
		{
			// all item entries after this date have to be included to the current billing cycle
			$this->dates['last_run'] = $last_run->created_at;

			// Separate invoice_nrs for every SepaAccount
			foreach ($sepa_accs as $acc)
			{
				// restart invoice nr counter every year
				if ($this->dates['m'] == '01')
				{
					if ($conf->invoice_nr_start)
						$acc->invoice_nr = $conf->invoice_nr_start;
					continue;
				}

				$tmp = DB::table($this->tablename)->where('sepa_account_id', '=', $acc->id)->orderBy('invoice_nr', 'desc')->select('invoice_nr')->first();
				$acc->invoice_nr = $tmp->invoice_nr;
			}
		}
		// first run for this system
		else
		{
			$this->dates['last_run'] = $this->dates['null'];

			foreach ($sepa_accs as $acc)
			{
				if ($conf->invoice_nr_start)
					$acc->invoice_nr = $conf->invoice_nr_start;
			}
		}

		$this->logger->addDebug('Last run was on '.$this->dates['last_run']);

		/*
		 * Loop over all Contracts
		 */
		foreach ($contracts as $c)
		{
			// debugging output
			// var_dump($c->id, round(microtime(true) - $start, 4));

			// check validity of contract
			if (!$c->check_validity($this->dates))
			{
				$this->logger->addNotice('Contract '.$c->number.' is out of date', [$c->id]);
				continue;				
			}

			if (!$c->create_invoice)
			{
				$this->logger->addInfo('Create invoice for Contract '.$c->number.' is off', [$c->id]);
				continue;
			}

			$charge 	= []; 					// total costs for this month for current contract
			$c->expires = date('Y-m', strtotime($c->contract_end)) == $this->dates['this_m'];


			/*
			 * Add internet, voip and tv tariffs and all other items and calculate price for current month considering 
			 * contract start & expiration date, calculate total sum/charge of items for booking records
			 */
			foreach ($c->items as $item)
			{
				// check validity
				if (!$item->check_validity($this->dates))
					continue;

				// only 1 internet & voip tariff !
				if ($item->product->type == 'Internet' && $c->get_valid_tariff('Internet') && $item->product_id != $c->get_valid_tariff('Internet')->id)
					continue;

				if ($item->product->type == 'Voip' && $c->get_valid_tariff('Voip') && $item->product_id != $c->get_valid_tariff('Voip')->id)
					continue;


				$costcenter = $item->product->costcenter ? $item->product->costcenter : $c->costcenter;
				$ret = $item->calculate_price_and_span($this->dates, $costcenter, $c->expires);
				
				$price = $ret['price'];
				// skip adding item to accounting records and bill if price == 0
				if (!$price)
					continue;

				// get account via costcenter
				$acc_id = $costcenter->sepa_account_id;
				$acc 	= $sepa_accs->find($acc_id);
				$text   = $ret['text'];


				// increase invoice nr of account, increase charge for account by price, calculate tax
				if (isset($charge[$acc_id]))
				{
					$charge[$acc_id]['net'] 	+= $price;
					$charge[$acc_id]['tax'] 	+= $item->product->tax ? $price * $conf->tax/100 : 0;
				}
				else
				{
					$charge[$acc_id]['net'] 	= $price;
					$charge[$acc_id]['tax'] 	= $item->product->tax ? $price * $conf->tax/100 : 0;
					$acc->invoice_nr += 1;
				}

				// save to accounting table as backup for future checking - NOTE: invoice nr counters are set from that table
				$count = $item->count ? $item->count : 1;
				DB::update('INSERT INTO '.$this->tablename.' (created_at, contract_id, name, product_id, ratio, count, invoice_nr, sepa_account_id) VALUES(NOW(),'.$c->id.',"'.$item->name.'",'.$item->product->id.','.$ret['ratio'].','.$count.','.$acc->invoice_nr.','.$acc_id.')');

				// add item to accounting records of account
				$acc->add_accounting_record($item, round($price, 2), $text);

				// create bill for account and contract and add item
				$acc->add_invoice_item($c, $conf, $count, round($price, 2), $text);

				// add
				if ($c->salesman)
					$salesmen->find($c->salesman->id)->add_item($item, $price);

			} // end of item loop

			// get actual valid sepa mandate
			$mandate = $c->get_valid_mandate();

			if (!$mandate)
				$this->logger->addNotice('Contract '.$c->number.' has no valid sepa mandate', [$c->id]);


			// Add billing file entries
			foreach ($charge as $acc_id => $value)
			{
				$value['net'] = round($value['net'], 2);
				$value['tax'] = round($value['tax'], 2);

				$acc = $sepa_accs->find($acc_id);
				$acc->add_booking_record($c, $mandate, $value, $conf);
				$acc->add_bill_data($c, $mandate, $value, $this->logger);

				// make bill already
				$acc['invoices'][$c->id]->make_invoice();

				// skip sepa part if contract has no valid mandate
				if (!$mandate)
					continue;

				$acc->add_sepa_transfer($mandate, $value['net'] + $value['tax'], $this->dates);
			}

		} // end of loop over contracts

		// store all billing files besides invoices
		if (!is_dir($this->dir))
			mkdir($this->dir, '0700');		
		$dir = $this->dir.date('Y_m').'/';
		if (!is_dir($dir))
			mkdir($dir, '0744');

		foreach ($sepa_accs as $acc)
			$acc->make_billing_files($dir);

		$file = $dir.'salesmen_commission.txt';
		File::put($file, "ID\tName\tCommission in %\tCommission Amount\tItems\n");

		foreach ($salesmen as $sm)
			$sm->print_commission($file);

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

// $this->logger = new Logger('Billing');
// $this->logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);

// switch ($this->argument('cycle'))
// {
// 	case 1:
// $this->logger->addInfo('Cycle without TV items/products');