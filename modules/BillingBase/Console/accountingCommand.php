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

class accountingCommand extends Command {

	/**
	 * The console command & table name, description, data arrays
	 *
	 * @var string
	 */
	protected $name 		= 'nms:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';
	
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
		// $this->logger = new Logger('Billing');
		// $this->logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);

		$this->dates = array(
			'today' 		=> date('Y-m-d'),
			'm' 			=> date('m'),
			'Y' 			=> date('Y'),
			'this_m'	 	=> date('Y-m'),
			'this_m_bill'	=> date('m/Y'),
			'last_m'		=> date('m', strtotime("first day of last month")),			// written this way because of known bug
			'last_m_Y'		=> date('Y-m', strtotime("first day of last month")),			// written this way because of known bug
			'last_m_bill'	=> date('m/Y', strtotime("first day of last month")),
			'null' 			=> '0000-00-00',
			'lastm_01' 		=> date('Y-m-01', strtotime("first day of last month")),
			'thism_01'		=> date('Y-m-01'),
			'nextm_01' 		=> date('Y-m-01', strtotime("+1 month")),
			'last_run' 		=> '',
			'm_in_sec' 		=> 60*60*24*30,			// month in seconds
		);

		parent::__construct();
	}



	/**
	 * Create invoice-, booking records and sepa xml file
	 * Execute the console command - Pay Attention to arguments
	 	* 1 - executed without TV items
	 	* 2 - only TV items
	 	* everything else - both are calculated for bills
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function fire()
	{
		$this->logger->addInfo(' #####    Start Accounting Command    #####');

		// switch ($this->argument('cycle'))
		// {
		// 	case 2:
		// 		$this->logger->addInfo('Cycle only for TV items/products');
		// 		break;
		// 	case 1:
		// 		$this->logger->addInfo('Cycle without TV items/products');
		// 	default:

		// remove all entries of this month from accounting table if entries were already created (and create them new)
		$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this->dates['thism_01'])->where('created_at', '<=', $this->dates['nextm_01'])->first();
		if (is_object($actually_created))
		{
			$this->logger->addNotice('Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');
			DB::update('DELETE FROM '.$this->tablename.' WHERE created_at>='.$this->dates['thism_01']);
		}

		// 		break;
		// }


		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();


		// check date of last run and get last invoice nr for each account
		$last_run = DB::table($this->tablename)->orderBy('created_at', 'desc')->select('created_at')->first();
		if (is_object($last_run))
		{
			// all item entries after this date have to be included to the current billing cycle
			$this->dates['last_run'] = $last_run->created_at;

			// Separate invoice_nrs for every SepaAccount
			foreach ($sepa_accs as $acc)
			{
				// start invoice nr counter every year new
				if ($this->dates['m'] == '01')
					continue;

				$invoice_nr = DB::table($this->tablename)->where('sepa_account_id', '=', $acc->id)->orderBy('invoice_nr', 'desc')->select('invoice_nr')->first();
				$acc->invoice_nr = $invoice_nr->invoice_nr;
			}
		}
		// first run for this system
		else
			$this->dates['last_run'] = $this->dates['null'];


		$this->logger->addDebug('Last run was on '.$this->dates['last_run']);


		/*
		 * Loop over all Contracts
		 */
		foreach (Contract::all() as $c)
		{
			// debugging output
			var_dump($c->id);

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

			// variable resets or incrementations
			$charge 	= []; 					// total costs for this month for current contract
			$c->expires = (date('Y-m', strtotime($c->contract_end)) == $this->dates['this_m']);


			/*
			 * Add internet, voip and tv tariffs and all other items and calculate price for this month considering 
			 * contract start & expiration date, calculate total sum of items for booking records
			 */
			foreach ($c->items as $item)
			{
				// check validity
				if (!$item->check_validity($this->dates))
					continue;

				// only TV items for this walk (when argument=2)
				// if ($this->argument('cycle') == 2 && $item->product->type != 'TV')
				// 	continue;
				// if ($this->argument('cycle') == 1 && $item->product->type == 'TV')
				// 	continue;


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

				// save to accounting table as backup for future checking
				$count = $item->count ? $item->count : 1;
				DB::update('INSERT INTO '.$this->tablename.' (created_at, contract_id, name, product_id, ratio, count, invoice_nr, sepa_account_id) VALUES(NOW(),'.$c->id.',"'.$item->name.'",'.$item->product->id.','.$ret['ratio'].','.$count.','.$acc->invoice_nr.','.$acc_id.')');

				// add item to accounting records of account
				$acc->add_accounting_record($item, round($price, 2), $text);

				// create bill for account and contract and add item
				$acc->add_bill_item($c, $conf, $count, round($price, 2), $text);

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
				$acc['bills'][$c->id]->make_bill();

				// skip sepa part if contract has no valid mandate
				if (!$mandate)
					continue;

				$acc->add_sepa_transfer($mandate, $value['net'] + $value['tax'], $this->dates);
			}
// if ($c->id == 500008)
// 	dd($sepa_accs[1]);


		} // end of loop over contracts

		// store all billing files besides invoices
		if (!is_dir(storage_path('billing')))
			mkdir(storage_path('billing'));

		foreach ($sepa_accs as $acc)
			$acc->make_billing_files();
	}



	/**
	 * Get the console command arguments / options
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
		];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}