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
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\SettlementRun;


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
		// $start = microtime(true);

		$logger = new BillingLogger;
		$logger->addInfo(' #####    Start Accounting Command    #####');

		$conf 		= BillingBase::first();
		$last_settlementrun = SettlementRun::withTrashed()->orderBy('id', 'desc')->get()->first();
		$sepa_accs  = SepaAccount::all();

		$contracts  = Contract::orderBy('number')->with('items', 'items.product', 'costcenter')->get();		// eager loading for better performance
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
			if (!$c->check_validity() && !(isset($cdrs[$c->id]) || isset($cdrs[$c->number])))
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
				if (!$item->check_validity($item->get_billing_cycle() == 'Yearly' ? 'year' : 'month', $item->is_tariff()))
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
				$acc->add_invoice_item($item, $conf, $last_settlementrun->id);
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

				$acc = $sepa_accs->find($c->costcenter->sepaaccount_id);

				// increase charge for booking record
				// Keep this order in case we need to increment the invoice nr if only cdrs are charged for this contract
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

				// accounting record
				$rec = new AccountingRecord;
				$rec->add_cdr($c, $acc, $charge, $calls);
				$acc->add_cdr_accounting_record($c, $charge, $calls);

				// invoice
				$acc->add_invoice_cdr($c, $cdrs[$id], $conf, $last_settlementrun->id);


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

		// performance analysis debugging output
		echo "time needed: ".round(microtime(true) - $start, 4)."\n";
	}


	/**
	 * (1) Clear/Create (Prepare) Directories
	 *
	 * (2) Initialise models for this billing cycle (could also be done during runtime but with performance degradation)
	 	* invoice number counter
	 	* storage directories
	 * Set Language for Billing
	 * Remove already created Invoice Database Entries
	 */
	private function _init($sepa_accs, $salesmen, $conf)
	{
		// set language for this run
		\App::setLocale($conf->userlang);

		// create directory structure
		if (is_dir(storage_path('app/'.$this->dir)))
			$this->_directory_cleanup();
		else
			mkdir(storage_path('app/'.$this->dir, 0700, true));

		// Salesmen
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $key => $sm)
			$sm->all_prod_types = $prod_types;

		// directory to save file - is actually only needed for first salesmen
		if (isset($salesmen[0])) $salesmen[0]->dir = $this->dir;


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


	/**
	 * This function removes all "old" files created by the previous called Command
	 * This is necessary because otherwise e.g. after deleting contracts the invoice would be kept and is still shown
	 * in customer control centre
	 */
	private function _directory_cleanup()
	{
		// Delete all invoices
		Invoice::delete_current_invoices();

		// everything in accounting directory - SepaAccount specific
		foreach (Storage::files($this->dir) as $f)
		{
			// keep cdr
			// if (pathinfo($f, PATHINFO_EXTENSION) != 'csv')
			if (basename($f) != $this->_get_cdr_filename())
				Storage::delete($f);
		}

		foreach (Storage::directories($this->dir) as $d)
			Storage::deleteDirectory($d);
			
	}


	/*
	 * Stores all billing files besides invoices in the directory defined as property of this class
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
	 * @return String 	Filename   e.g.: 'Call Data Record_2016_08.csv' or if app language is german 'Einzelverbindungsnachweis_2015_01.csv'
	 */
	private function _get_cdr_filename()
	{
		$offset = BillingBase::first()->cdr_offset;
		$time = $offset ? strtotime('-'.($offset+1).' month') : strtotime('first day of last month');

		return \App\Http\Controllers\BaseViewController::translate_label('Call Data Record').'_'.date('Y_m', $time).'.csv';
	}


	/**
	 * Calls cdrCommand to get Call data records from Provider and formats relevant data to structured array
	 *
	 * @return array 	[contract_id => [phonr_nr, time, duration, ...], 
	 *					 next_contract_id => [...],
	 * 					 ...]
	 *					on success, else 2 dimensional empty array
	 */
	private function _get_cdr_data()
	{
		$filename = $this->_get_cdr_filename();
		$dir_path = storage_path('app/'.$this->dir.'/');
		$filepath = $dir_path.$filename;

		if (!is_file($filepath))
		{
			// get call data records
			$ret = $this->call('billing:cdr');

			if ($ret)
				return array(array());
		}


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
	 * @return array  [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_envia_csv($filepath)
	{
		$csv = is_file($filepath) ? file($filepath) : array(array());

		// skip first line (column description)
		if ($csv[0])
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
	 * @return array 	[contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_hlkomm_csv($filepath)
	{
		$csv = is_file($filepath) ? file($filepath) : array(array());

		// skip first 5 lines (descriptions)
		if ($csv[0])
			unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);
		else
			return $csv;

		// get phonenr to contract_id listing - needed because only phonenr is mentioned in csv
		// select m.contract_id, a.username from phonenumber a, mta b, modem m where a.mta_id=b.id AND b.modem_id=m.id order by m.contract_id;
		$phonenumbers_o = \DB::table('phonenumber')
			->join('mta', 'phonenumber.mta_id', '=', 'mta.id')
			->join('modem', 'modem.id', '=', 'mta.modem_id')
			->select('modem.contract_id', 'phonenumber.username')
			->orderBy('modem.contract_id')->get();

        foreach ($phonenumbers_o as $value)
			$phonenrs[$value->username] = $value->contract_id;


		// create structured array
		foreach ($csv as $line)
		{
			$line = str_getcsv($line, '\t');
			$phonenr1 = $line[4].$line[5].$line[6];			// calling nr
			$phonenr2 = $line[7].$line[8].$line[9];			// called nr

			// TODO: simplify after checking 2nd case!
			if (isset($phonenrs[$phonenr1]))
				$data[$phonenrs[$phonenr1]][] = array($phonenr1, $line[0], $line[1], $line[10], $phonenr2, $line[13]);
			else if (isset($phonenrs[$phonenr2]))
				// our phonenr is the called nr - TODO: proof if this case can actually happen - normally this shouldnt be the case
				$data[$phonenrs[$phonenr2]][] = array($phonenr1, $line[0], $line[1], $line[10], $phonenr2, $line[13]);
			else
			{
				// there is a phonenr entry in csv that doesnt exist in our db - this case should never happen
				$logger = new BillingLogger;
				$logger->addError('Parse CDR.csv: Call Data Record with Phonenr that doesnt exist in the Database - Phonenr deleted?');
			}

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
