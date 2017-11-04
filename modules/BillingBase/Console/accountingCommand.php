<?php
namespace Modules\BillingBase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Storage;
use DB;

use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\AccountingRecord;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\Salesman;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Http\Controllers\SettlementRunController;
use ChannelLog as Log;


class accountingCommand extends Command implements SelfHandling, ShouldQueue {

	use InteractsWithQueue, SerializesModels;


	/**
	 * The console command & table name, description, data arrays
	 *
	 * @var string
	 */
	public $name 		= 'billing:accounting';
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
		$this->dates = self::create_dates_array();
		$this->dir .= date('Y-m', strtotime('first day of last month')).'/';

		parent::__construct();
	}



	/**
	 * Execute the console command: Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
	 *
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function handle()
	{
		// $start = microtime(true);

		if (\App::runningInConsole())
		{
			Log::debug('billing', ' #####    Start Accounting Command from Console   #####');
			// create/update settlementrun model when we run from console
			$sr = SettlementRun::where('year', '=', $this->dates['Y'])->where('month', '=', (int) $this->dates['lastm'])->orderBy('id', 'desc')->get()->all();

			if (!$sr)
			{
				Log::debug('billing', 'Add new SettlementRun and Return as Observer will call this Cmd again');
				SettlementRun::create(['year' => $this->dates['Y'], 'month' => $this->dates['lastm']]);
				return;
			}
			else
			{
				$sr = $sr[0];
				$settlementrun_id = $sr->id;
				$sr->update(['updated_at' => date('Y-m-d H:i:s')]);
				Log::debug('billing', 'Update existing SettlementRun', [$sr->id]);
			}
		}
		else
		{
			Log::debug('billing', ' #####    Start Accounting Command via GUI   #####');
			$settlementrun_id = SettlementRun::orderBy('id', 'desc')->get()->first()->id;
			Log::debug('billing', 'SettlementRun already created through GUI');
		}

		// Fetch all Data from Database
		echo "Get all Data from Database\n";
		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();

		$contracts  = Contract::orderBy('number')->with('items', 'items.product', 'costcenter', 'sepamandates')->get();		// eager loading for better performance
		$salesmen 	= Salesman::all();

		if (!isset($sepa_accs[0])) {
			Log::error('billing', 'There are no Sepa Accounts to create Billing Files for - Stopping here!');
			throw new Exception("There are no Sepa Accounts to create Billing Files for");
		}

		// init product types of salesmen and invoice nr counters for each sepa account, date of last run
		$this->_init($sepa_accs, $salesmen, $conf);

		// get call data records as ordered structure (array)
		$cdrs = $this->_get_cdr_data();
		if (!$cdrs)
			Log::warning('billing', 'No Call Data Records available for this Run!');

		echo "Create Invoices:\n";
		$num = count($contracts);
		// if not called silently via queues
		if ($this->output)
			$bar = $this->output->createProgressBar($num);

		/*
		 * Loop over all Contracts
		 */
		foreach ($contracts as $i => $c)
		{
			// progress bar - workaround as progress bar is not shown when cmd is called
			// from observer or throws exception when called via queue
			if ($this->output)
				$bar->advance();
			else
				echo ($i + 1)."/$num [$c->id]\r";

			if (!$c->create_invoice) {
				Log::info('billing', "Create invoice for Contract $c->number [$c->id] is off");
				continue;
			}

			if (!$c->costcenter) {
				Log::error('billing', "Contract $c->number [$c->id] has no CostCenter assigned - Stop execution");
				throw new Exception("Contract $c->number [$c->id] has no CostCenter assigned", 1);
				continue;
			}

			// Skip invalid contracts
			if (!$c->check_validity('yearly') && !(isset($cdrs[$c->id]) || isset($cdrs[$c->number]))) {
				Log::info('billing', "Contract $c->number [$c->id] is invalid for current year");
				continue;
			}


			/*
			 * Collect item specific data for all billing files
			 */
			foreach ($c->items as $item)
			{
				// skip items that are related to a deleted product
				if (!isset($item->product)) {
					Log::error('billing', "Product of $item->accounting_text was deleted", [$c->id]);
					throw new \Exception("Product of $item->accounting_text was deleted");
					continue;
				}

				// skip if price is 0 (or item dates are invalid)
				if (!($ret = $item->calculate_price_and_span($this->dates))) {
					Log::info('billing', 'Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
					continue;
				}

				// get account via costcenter
				$costcenter = $item->get_costcenter();
				$acc 		= $sepa_accs->find($costcenter->sepaaccount_id);

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
				$acc->add_invoice_item($item, $conf, $settlementrun_id);
				if ($c->salesman_id)
					$salesmen->find($c->salesman_id)->add_item($item);

			} // end of item loop


			/**
			 * Add Call Data Records - calculate charge and count
			 */
			$charge = $calls = $id = 0;

			if (isset($cdrs[$c->id]))
				$id = $c->id;
			else if (isset($cdrs[$c->number]))
				$id = $c->number;

			if ($id)
			{
				foreach ($cdrs[$id] as $entry) {
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
					// this case should only happen when contract/voip tarif ended and deferred CDRs are calculated
					Log::notice('billing', 'Contract '.$c->number.' has Call Data Records but no valid Voip Tariff assigned', [$c->id]);
					$c->charge[$acc->id]['net'] = $charge;
					$c->charge[$acc->id]['tax'] = $charge * $conf->tax/100;
					$acc->invoice_nr += 1;
				}

				// accounting record
				$rec = new AccountingRecord;
				$rec->add_cdr($c, $acc, $charge, $calls);
				$acc->add_cdr_accounting_record($c, $charge, $calls);

				// invoice
				$acc->add_invoice_cdr($c, $cdrs[$id], $conf, $settlementrun_id);
			}

			/*
			 * Add contract specific data for accounting files
			 */

			// get actual globally valid sepa mandate (valid for all CostCenters/SepaAccounts)
			$mandate_global = $c->get_valid_mandate();

			foreach ($c->charge as $acc_id => $value)
			{
				$value['net'] = round($value['net'], 2);
				$value['tax'] = round($value['tax'], 2);

				$acc = $sepa_accs->find($acc_id);

				$mandate_specific = $c->get_valid_mandate('now', $acc->id);
				$mandate = $mandate_specific ? : $mandate_global;

				$acc->add_booking_record($c, $mandate, $value, $conf);
				$acc->set_invoice_data($c, $mandate, $value);

				// create invoice pdf already - this task is the most timeconsuming and therefore threaded!
				$acc['invoices'][$c->id]->make_invoice();

				// skip sepa part if contract has no valid mandate
				if (!$mandate) {
					Log::info('billing', "Contract $c->number [$c->id] has no valid sepa mandate for SepaAccount $acc->name [$acc->id]");
					continue;
				}

				$acc->add_sepa_transfer($mandate, $value['net'] + $value['tax'], $this->dates);
			}

		} // end of loop over contracts

		echo "\n";

		// avoid deleting temporary latex files before last invoice was built (multiple threads are used)
		// and wait for all invoice pdfs to be created for concatenate them in zip command in _make_billing_files()
		usleep(200000);

		$this->_make_billing_files($sepa_accs, $salesmen);
		Invoice::remove_templatex_files();
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

		// create directory structure and remove old invoices
		if (is_dir(storage_path('app/'.$this->dir)))
			SettlementRunController::directory_cleanup($this->dir);
		else
			mkdir(storage_path('app/'.$this->dir), 0700, true);

		// Salesmen
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $key => $sm)
		{
			$sm->all_prod_types = $prod_types;
			$sm->dir = $this->dir;
		}
		// directory to save file - is actually only needed for first salesmen
		// if (isset($salesmen[0])) $salesmen[0]->dir = $this->dir;


		// SepaAccount
		foreach ($sepa_accs as $acc)
		{
			$acc->dir = $this->dir;
			$acc->rcd = $conf->rcd ? date('Y-m-'.$conf->rcd) : date('Y-m-d', strtotime('+1 day'));
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
				if ($this->dates['lastm'] == '01')
				{
					if ($acc->invoice_nr_start)
						$acc->invoice_nr = $acc->invoice_nr_start - 1;
					continue;
				}

				$nr = AccountingRecord::where('sepa_account_id', '=', $acc->id)->orderBy('invoice_nr', 'desc')->select('invoice_nr')->first();
				if (is_object($nr))
					$acc->invoice_nr = $nr->invoice_nr;
				else
					$acc->invoice_nr = $acc->invoice_nr_start;
			}
		}
		// first run for this system
		else
		{
			foreach ($sepa_accs as $acc)
			{
				if ($acc->invoice_nr_start)
					$acc->invoice_nr = $acc->invoice_nr_start - 1;
			}
		}

		// reset yearly payed items payed_month column
		if ($this->dates['lastm'] == '01')
			Item::where('payed_month', '!=', '0')->update(['payed_month' => '0']);

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

			// delete file if there are no entries
			if (Storage::size($salesmen[0]->get_storage_rel_filename()) <= 60)
				Storage::delete($salesmen[0]->get_storage_rel_filename());
		}

		// create zip file
		echo "ZIP all Files";
		\Artisan::call('billing:zip');
	}


	/**
	 * @return String 	Filename   e.g.: 'Call Data Record_2016_08.csv' or if app language is german 'Einzelverbindungsnachweis_2015_01.csv'
	 */
	public static function _get_cdr_filename()
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
		$filename = self::_get_cdr_filename();
		$dir_path = storage_path('app/'.$this->dir.'/');
		$filepath = $dir_path.$filename;

		if (!is_file($filepath))
		{
			// get call data records
			$ret = \Artisan::call('billing:cdr');

			if ($ret)
				return array(array());
		}

		// NOTE: Add new Providers here!
		if (env('PROVVOIPENVIA__RESELLER_USERNAME'))
		{
			return $this->_parse_envia_csv($filepath);
		}

		else if (env('HLKOMM_RESELLER_USERNAME'))
		{
			return $this->_parse_hlkomm_csv($filepath);
		}

		else
			// we could throw an redundant exception here as well - is already thrown in cdrCommand
			return array(array());

	}



	/**
	 * Parse Envia CSV and Check if customerNr to Phonenr assignment exists
	 *
	 * @return array  [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_envia_csv($filepath)
	{
		Log::debug('billing', 'Parse Envia Call Data Records CSV');

		$csv = is_file($filepath) ? file($filepath) : null;

		if (!$csv)
			return array(array());

		/*
		 * Order existing phonenumbers in format 03735 739822 (prefix, number) to contract id/number as structured array:
		 * 		[pn1 => [id, num], pn2 => [...], ...]
		 * needed to check later if customer can really have made these calls (if customer number to phonenumber assignment is correct)
		 * NOTE: customer number here means the envia customer number that corresponds to id OR number in our database
		 */
		$phonenumbers_db = \DB::table('phonenumber')
			->join('mta', 'phonenumber.mta_id', '=', 'mta.id')
			->join('modem', 'modem.id', '=', 'mta.modem_id')
			->join('contract', 'contract.id', '=', 'modem.contract_id')
			->where('phonenumber.deleted_at', '=', null)
			->select('modem.contract_id', 'contract.number', 'phonenumber.username', 'phonenumber.id')
			->orderBy('modem.contract_id')->get();

		foreach ($phonenumbers_db as $key => $pn)
		{
			if (substr($pn->username, 0, 1) != '0') {
				// can be a poorly disabled testnumber -> discard
				Log::warning('billing', "Username [$pn->username] of Phonenumber ID [$pn->id] not in correct format");
				continue;
			}

			$customer_nrs[substr_replace($pn->username, '49', 0, 1)] = [$pn->contract_id, $pn->number];

			$customer_nrs_array[] = $pn->contract_id;
			$customer_nrs_array[] = $pn->number;
		}

		// skip first line of csv (column description)
		$logged = '';
		unset($csv[0]);

		foreach ($csv as $line)
		{
			$line = str_getcsv($line, ';');
			$customer_nr 	= intval(str_replace('002-', '', $line[0]));
			$calling_number = $line[3];
			$called_number  = $line[7];

			if (!isset($customer_nrs[$calling_number]))
			{
				if (in_array($customer_nr, $customer_nrs_array)) {
					Log::error('billing', "Calling Number [$calling_number] does not exist in our DB for customer number $customer_nr! Exit");
					throw new \Exception("Calling Number [$calling_number] does not exist in our DB for customer number $customer_nr! Exit");
				}

				if ($logged != $calling_number) {
					Log::warning('billing', "Calling Number [$calling_number] does not exist - but customer number [$customer_nr] neither!");
					$logged = $calling_number;
				}
				continue;
			}

			if (!in_array($customer_nr, $customer_nrs[$calling_number])) {
				Log::error('billing', "Calling Number [$calling_number] has different envia customer number [$customer_nr] than it has in the local Database! Exit");
				// $this->error("Calling Number [$calling_number] has different envia customer number [$customer_nr] than it has in the local Database!");
				throw new \Exception("Calling Number [$calling_number] has different envia customer number [$customer_nr] than it has in the local Database!");
			}

			$data[$customer_nr][] = array($calling_number, substr($line[4], 4).'-'.substr($line[4], 2, 2).'-'.substr($line[4], 0, 2) , $line[5], $line[6], $called_number, str_replace(',', '.', $line[10]));
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
		$csv = is_file($filepath) ? file($filepath) : null;

		if (!$csv)
			return array(array());

		// skip first 5 lines (descriptions)
		unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);

		$config = BillingBase::first();

		// get phonenr to contract_id listing - needed because only phonenr is mentioned in csv
		// select m.contract_id, a.username from phonenumber a, mta b, modem m where a.mta_id=b.id AND b.modem_id=m.id order by m.contract_id;
		$phonenumbers_o = \DB::table('phonenumber')
			->join('mta', 'phonenumber.mta_id', '=', 'mta.id')
			->join('modem', 'modem.id', '=', 'mta.modem_id')
			->where('phonenumber.deleted_at', '=', null)
			->select('modem.contract_id', 'phonenumber.username')
			->orderBy('modem.contract_id')->get();

        foreach ($phonenumbers_o as $value)
        {
        	if ($value->username)
        	{
        		if (substr($value->username, 0, 4) == '0049')
					$phonenrs[substr_replace($value->username, '49', 0, 4)] = $value->contract_id;
        	}
        }

		// create structured array
		foreach ($csv as $line)
		{
			$line = str_getcsv($line, "\t");
			$phonenr1 = $line[4].$line[5].$line[6];			// calling nr
			$phonenr2 = $line[7].$line[8].$line[9];			// called nr

			$a = array($phonenr1, $line[0], $line[1], $line[10], $phonenr2, str_replace(',', '.', $line[13]));

			// calculate price with hlkomms distance zone
			// $a[5] = strpos($line[3], 'Mobilfunk national') !== false ? $a[5] * ($config->voip_extracharge_mobile_national / 100 + 1) : $a[5] * ($config->voip_extracharge_default / 100 + 1);
			$a[5] = $line[15] == '990711' ? $a[5] * ($config->voip_extracharge_mobile_national / 100 + 1) : $a[5] * ($config->voip_extracharge_default / 100 + 1);

			if (isset($phonenrs[$phonenr1]))
				$data[$phonenrs[$phonenr1]][] = $a;
			else if (isset($phonenrs[$phonenr2]))
				// our phonenr is the called nr - TODO: proof if this case can actually happen - normally this shouldnt be the case
				$data[$phonenrs[$phonenr2]][] = $a;
			else
			{
				// there is a phonenr entry in csv that doesnt exist in our db - this case should never happen
				Log::error('billing', "Parse CDR.csv: Call Data Record with Phonenr [$phonenr1] that doesnt exist in the Database - Phonenr deleted?");
			}

		}

		return $data;
	}


	/**
	 * Instantiates an Array of all necessary date formats needed during execution of this Command
	 *
	 * Also needed in Item::calculate_price_and_span and in DashboardController!!
	 *
	 * TODO: Maybe implement this as service Provider or just dont use it
	 */
	public static function create_dates_array()
	{
		return array(

			'today' 		=> date('Y-m-d'),
			'm' 			=> date('m'),
			'Y' 			=> date('Y', strtotime("first day of last month")),

			'this_m'	 	=> date('Y-m'),
			'thism_01'		=> date('Y-m-01'),
			'thism_bill'	=> date('m/Y'),

			'lastm'			=> date('m', strtotime("first day of last month")),			// written this way because of known bug ("-1 month" or "last month" is erroneous)
			'lastm_01' 		=> date('Y-m-01', strtotime("first day of last month")),
			'lastm_bill'	=> date('m/Y', strtotime("first day of last month")),
			'lastm_Y'		=> date('Y-m', strtotime("first day of last month")),		// strtotime(first day of last month) is integer with actual timestamp!

			'nextm_01' 		=> date('Y-m-01', strtotime("+1 month")),

			'null' 			=> '0000-00-00',
			'm_in_sec' 		=> 60*60*24*30,						// month in seconds
			'last_run'		=> '0000-00-00', 					// filled on start of execution

		);
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
			// array('debug', null, InputOption::VALUE_OPTIONAL, 'Print Debug Output to Commandline (1 - Yes, 0 - No (Default))', 0),
		];
	}

}
