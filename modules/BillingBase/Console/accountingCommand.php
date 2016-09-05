<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Storage;
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
	protected $dir 			= 'data/billingbase/accounting/'; 				// relative to storage/app/ - Note: completed by month in constructor!
	
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

			'lastm'			=> date('m', strtotime("first day of last month")),			// written this way because of known bug ("-1 month" or "last month" is erroneous)
			'lastm_01' 		=> date('Y-m-01', strtotime("first day of last month")),
			'lastm_bill'	=> date('m/Y', strtotime("first day of last month")),
			'lastm_Y'		=> date('Y-m', strtotime("first day of last month")),		// strtotime(first day of last month) is integer with actual timestamp!

			'nextm_01' 		=> date('Y-m-01', strtotime("+1 month")),

			'null' 			=> '0000-00-00',
			'm_in_sec' 		=> 60*60*24*30,			// month in seconds
			'last_run'		=> '0000-00-00', 					// filled on start of execution

		);

		$this->dir .= date('Y-m', strtotime('first day of last month')).'/';

		parent::__construct();

	}



	/**
	 * Execute the console command: Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
	 *
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function fire()
	{
		$logger = new BillingLogger;
		$logger->addInfo(' #####    Start Accounting Command    #####');

		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();
		$contracts  = Contract::with('items', 'items.product', 'costcenter')->get();		// eager loading for better performance
		$salesmen 	= Salesman::all();


		if (!isset($sepa_accs[0]))
		{
			$logger->addError('There are no Sepa Accounts to create Billing Files for - Stopping here!');
			return -1;
		}

		// remove all entries of this month permanently (if already created)
		$ret = AccountingRecord::whereBetween('created_at', [$this->dates['thism_01'], $this->dates['nextm_01']])->forceDelete();
		if ($ret)
			$logger->addNotice('Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');


		// init product types of salesmen and invoice nr counters for each sepa account, date of last run
		$this->_init($sepa_accs, $salesmen, $conf);

		// get call data records as ordered structure (array)
		$cdrs = $this->_get_cdr_data();
		if (!$cdrs)
			$logger->addAlert('No Call Data Records available for this Run!');
		
		/*
		 * Loop over all Contracts
		 */
		foreach ($contracts as $c)
		{
			// debugging output
			var_dump($c->id); //, round(microtime(true) - $start, 4));


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

			if(!$c->costcenter)
			{
				$logger->addAlert('Contract '.$c->number.' has no CostCenter assigned - Stop execution for this contract', [$c->id]);
				continue;
			}

			$charge 	= []; 					// total costs for this month for current contract
			$c->expires = date('Y-m-01', strtotime($c->contract_end)) == $this->dates['lastm_01'];


			/*
			 * Collect item specific data for all billing files
			 */
			foreach ($c->items as $item)
			{
				// skip items that are related to a deleted product
				if (!isset($item->product))
				{
					$logger->addDebug('Product '.$item->accounting_text.' was deleted', [$c->id]);
					continue;
				}

				// skip invalid items
				if (!$item->check_validity($item->get_billing_cycle() == 'Yearly' ? 'year' : 'month'))
				{
					$logger->addDebug('Item '.$item->product->name.' is outdated', [$c->id]);
					continue;
				}

				// skip if price is 0
				if (!($ret = $item->calculate_price_and_span($this->dates)))
				{
					$logger->addDebug('Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
					continue;
				}

				// get account via costcenter
				$costcenter = $item->get_costcenter();
				$acc 		= $sepa_accs->find($costcenter->sepaaccount_id);

				// if ($c->number == 10003 && $item->product->type == 'Credit')
				// 	dd($item->charge);

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
				$rec->store_item($item, $acc);

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


			// Add Call Data Records - calculate charge and count
			$charge = $calls = $id = 0;

			if (isset($cdrs[$c->id]))
				$id = $c->id;
			else if (isset($cdrs[$c->number]))
				$id = $c->number;

			if ($id)
			{
				foreach ($cdrs[$id] as $entry)
				{
					$charge += $entry[5];
					$calls++;
				}

				// accounting record
				$acc = $sepa_accs->find($c->costcenter->sepaaccount_id);
				$rec = new AccountingRecord;
				$rec->add_cdr($c, $acc, $charge, $calls);
				$acc->add_cdr_accounting_record($c, $charge, $calls);

				// invoice
				$acc->add_invoice_cdr($c, $cdrs[$id], $conf);

				// increase charge for booking record
				if (isset($c->charge[$acc->id]))
				{
					$c->charge[$acc->id]['net'] += $charge;
					$c->charge[$acc->id]['tax'] += $charge * $conf->tax/100;
				}
				else
				{
					// this case should never happen
					$logger->addAlert('Contract '.$c->number.' has Call Data Records but no valid Voip Tariff assigned', [$c->id]);
					$c->charge[$acc->id]['net'] = $charge;
					$c->charge[$acc->id]['tax'] = $charge * $conf->tax/100;
					$acc->invoice_nr += 1;
				}

			}



			// Add contract specific data for accounting files
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



	/**
	 * Initialise models for this billing cycle (could also be done during runtime but with performance degradation)
	 	* invoice number counter
	 	* storage directories
	 */
	private function _init($sepa_accs, $salesmen, $conf)
	{
		// create directory structure
		if (!is_dir(storage_path('app/'.$this->dir)))
		{
			// mkdir(storage_path('app/'.$this->dir, '0700', true)); does not work?
			system('mkdir -p 0700 '.storage_path('app/'.$this->dir)); // system call because php mkdir creates weird permissions - umask couldnt solve it !?
		}

		// Salesmen
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $key => $sm)
		{
			$sm->all_prod_types = $prod_types;
			// directory to save file
			$sm->dir = $this->dir;
		}

		// SepaAccount
		foreach ($sepa_accs as $acc)
		{
			$acc->dir = $this->dir;
			$acc->rcd = $conf->rcd ? date('Y-m-'.$conf->rcd) : date('Y-m-d', strtotime('+5 days'));
		}

		// actual invoice nr counters
		$last_run = AccountingRecord::orderBy('created_at', 'desc')->select('created_at')->first();
		if (is_object($last_run))
		{
			// set time of last run
			$this->dates['last_run'] = $last_run->created_at;

			foreach ($sepa_accs as $acc)
			{
				// restart counter every year
				if ($this->dates['m'] == '01')
				{
					if ($conf->invoice_nr_start)
						$acc->invoice_nr = $conf->invoice_nr_start - 1;
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
					$acc->invoice_nr = $conf->invoice_nr_start - 1;
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

		if (isset($salesmen[0]))
		{
			$salesmen[0]->prepare_output_file();
			foreach ($salesmen as $sm)
				$sm->print_commission();
		}

		// create zip file
		\Artisan::call('billing:zip');
	}


	/**
	 * Calls cdrCommand to get Call data records from Provider and formats relevant data to structured array
	 *
	 * @return array 	[contract_id => [phonr_nr, time, duration, ...], 
	 *					 next_contract_id => [...],
	 * 					 ...]
	 *					on success, else empty 2 dimensional array
	 */
	private function _get_cdr_data()
	{
		$filename = 'cdr_'.date('Y_m', strtotime('-2 month')).'.csv';
		$dir_path = storage_path('app/'.$this->dir.'/');
		$filepath = $dir_path.$filename;
		
		if (!is_file($filepath))
			$ret = $this->call('billing:cdr');

		if ($ret)
			return array(array());


		// NOTE: Add new Providers here!
		if (isset($_ENV['PROVVOIPENVIA__RESELLER_USERNAME']))
		{
			return $this->_parse_envia_csv($filepath);
		}

		else if (isset($_ENV['HLKOMM_RESELLER_USERNAME']))
		{
			return $this->_parse_hlkomm_csv($filepath);
		}

		else
			// we could throw an redundant exception here as well - is already thrown in cdrCommand
			return array(array());

	}


	/**
	 * Parse Envia CSV
	 *
	 * @return array  [contract_id/contract_number => [Calling Number, Date, Start, Duration, Called Number, Price], ...]
	 */
	protected function _parse_envia_csv($filepath)
	{
		$csv = is_file($filepath) ? file($filepath) : array(array());

		// skip first line (column description)
		if (isset($csv[0]))
			unset($csv[0]);
		else
			return $csv;

		foreach ($csv as $line)
		{
			$line = str_getcsv($line, ';');
			$data[intval($line[0])][] = array($line[3], substr($line[4], 4).'-'.substr($line[4], 2, 2).'-'.substr($line[4], 0, 2) , $line[5], $line[6], $line[7], str_replace(',', '.', $line[10]));
		}

		return $data;
	}


	/**
	 * Parse HLKomm CSV
	 *
	 * @return array 	[contract_id/contract_number => [Calling Number, Date, Start, Duration, Called Number, Price], ...]
	 */
	protected function _parse_hlkomm_csv($filepath)
	{
		$csv = is_file($filepath) ? file($filepath) : array(array());

		// skip first 5 lines (column description)
		if (isset($csv[0]))
			unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);
		else
			return $csv;

		foreach ($csv as $line)
		{
			$line = str_getcsv($line, ';');
			// $data[intval($line[0])][] = array($line[3], substr($line[4], 4).'-'.substr($line[4], 2, 2).'-'.substr($line[4], 0, 2) , $line[5], $line[6], $line[7], str_replace(',', '.', $line[10]));
		}

		return $data;
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
