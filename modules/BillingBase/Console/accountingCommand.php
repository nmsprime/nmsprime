<?php
namespace Modules\BillingBase\Console;

use ChannelLog as Log, DB, Storage;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{ SerializesModels, InteractsWithQueue};
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\{ AccountingRecord, BillingBase, Invoice, Item, Product, Salesman, SepaAccount, SettlementRun};
use Modules\BillingBase\Http\Controllers\SettlementRunController;
use Symfony\Component\Console\Input\{ InputOption, InputArgument};
use App\Http\Controllers\BaseViewController;

class accountingCommand extends Command implements SelfHandling, ShouldQueue {

	use InteractsWithQueue, SerializesModels;


	/**
	 * The console command & table name, description, data arrays
	 *
	 * @var string
	 */
	public $name 			= 'billing:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';

	protected $dates;					// offen needed time strings for faster access - see constructor
	protected $sr;


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(SettlementRun $sr)
	{
		$this->sr = $sr;

		parent::__construct();
	}



	/**
	 * Execute the console command
	 *
	 * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
	 */
	public function handle()
	{
		// $start = microtime(true);
		$this->dates = self::create_dates_array();

		// Determine SR (SettlementRun) ID as this is necessary to create relation between Invoice & SR
		if (!$this->sr->getAttribute('id'))
			$this->sr = SettlementRun::where('year', '=', $this->dates['Y'])->where('month', '=', (int) $this->dates['lastm'])->orderBy('id', 'desc')->first();

		if (!$this->sr || !$this->sr->getAttribute('id')) {
			// Note: create will run the observer that calls this command again with this SR
			SettlementRun::create(['year' => $this->dates['Y'], 'month' => $this->dates['lastm']]);
			exit(0);
		}


		Log::debug('billing', ' #####    Start Accounting Command   #####');

		// Fetch all Data from Database
		echo "Get all Data from Database...\n";
		self::push_state(0, 'Load Data...');
		$conf 		= BillingBase::first();
		$sepa_accs  = SepaAccount::all();

		$contracts  = Contract::orderBy('number')->with('items', 'items.product', 'costcenter', 'sepamandates')->get();		// eager loading for better performance
		$salesmen 	= Salesman::all();

		if (!isset($sepa_accs[0])) {
			Log::error('billing', 'There are no Sepa Accounts to create Billing Files for - Stopping here!');
			throw new Exception("There are no Sepa Accounts to create Billing Files for");
		}

		// init product types of salesmen and invoice nr counters for each sepa account, date of last run
		$this->_init($sepa_accs, $salesmen);

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
			// progress bar on cmd line
			if ($this->output) {
				// NOTE: $bar->advance() throws exception when called via queue
				$bar->advance();
			}
			// progress bar in GUI
			else if (!($i % 10)) {
				self::push_state((int) $i/$num*100, 'Create Invoices');
				// echo ($i + 1)."/$num [$c->id][".(memory_get_usage()/1000000)."]\r";
			}

			if (!$c->create_invoice) {
				Log::info('billing', "Create invoice for Contract $c->number [$c->id] is off");
				continue;
			}

			// Skip invalid contracts
			if (!$c->check_validity('yearly') && !(isset($cdrs[$c->id]) || isset($cdrs[$c->number]))) {
				Log::debug('billing', "Contract $c->number [$c->id] is invalid for current year");
				continue;
			}

			if (!$c->costcenter) {
				Log::error('billing', "Contract $c->number [$c->id] has no CostCenter assigned - Stop execution");
				throw new \Exception("Contract $c->number [$c->id] has no CostCenter assigned", 1);
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
				}

				// skip if price is 0 (or item dates are invalid)
				if (!($ret = $item->calculate_price_and_span($this->dates))) {
					Log::debug('billing', 'Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
					continue;
				}

				// get account via costcenter
				$costcenter = $item->get_costcenter();
				$acc 		= $sepa_accs->find($costcenter->sepaaccount_id);

				// increase invoice nr of sepa account
				if (!isset($c->charge[$acc->id]))
				{
					$c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
					$acc->invoice_nr += 1;
				}

				// increase charge for account by price, calculate tax
				$c->charge[$acc->id]['net'] += $item->charge;
				$c->charge[$acc->id]['tax'] += $item->product->tax ? $item->charge * $this->conf->tax/100 : 0;

				$item->charge = round($item->charge, 2);

				// save to accounting table (as backup for future) - NOTE: invoice nr counters are set initially from that table
				$rec = new AccountingRecord;
				$rec->store_item($item, $acc);

				// add item to accounting records of account, invoice and salesman
				$acc->add_accounting_record($item);
				$acc->add_invoice_item($item, $this->conf, $this->sr->id);
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
					$charge += $entry['price'];
					$calls++;
				}

				$acc = $sepa_accs->find($c->costcenter->sepaaccount_id);

				// increase charge for booking record
				// Keep this order in case we need to increment the invoice nr if only cdrs are charged for this contract
				if (!isset($c->charge[$acc->id]))
				{
					// this case should only happen when contract/voip tarif ended and deferred CDRs are calculated
					Log::notice('billing', 'Contract '.$c->number.' has Call Data Records but no valid Voip Tariff assigned', [$c->id]);
					$c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
					$acc->invoice_nr += 1;
				}

				$c->charge[$acc->id]['net'] += $charge;
				$c->charge[$acc->id]['tax'] += $charge * $this->conf->tax/100;

				// accounting record
				$rec = new AccountingRecord;
				$rec->add_cdr($c, $acc, $charge, $calls);
				$acc->add_cdr_accounting_record($c, $charge, $calls);

				// invoice
				$acc->add_invoice_cdr($c, $cdrs[$id], $this->conf, $this->sr->id);
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

				$acc->add_booking_record($c, $mandate, $value, $this->conf);
				$acc->set_invoice_data($c, $mandate, $value);

				// create invoice pdf already - this task is the most timeconsuming and therefore threaded!
				$acc->invoices[$c->id]->make_invoice();
				unset($acc->invoices[$c->id]);

				// skip sepa part if contract has no valid mandate
				if (!$mandate) {
					Log::debug('billing', "Contract $c->number [$c->id] has no valid sepa mandate for SepaAccount $acc->name [$acc->id]");
					continue;
				}

				$acc->add_sepa_transfer($mandate, $value['net'] + $value['tax']);
			}

		} // end of loop over contracts

		echo "\n";

		// avoid deleting temporary latex files before last invoice was built (multiple threads are used)
		// and wait for all invoice pdfs to be created for concatenation in zipCommand@_make_billing_files()
		usleep(200000);

		// while removing it's tested if all PDFs were created successfully
		Invoice::remove_templatex_files();
		$this->_make_billing_files($sepa_accs, $salesmen);

		self::push_state(100, 'Finished');
	}



	/**
	 * @param  Integer if > 0 the pathname of the timestamps month is returned
	 * @return String  Absolute path of accounting directory for actual settlement run (when no argument is specified)
	 */
	public static function get_absolute_accounting_dir_path($timestamp = 0)
	{
		return storage_path('app/'.self::get_relative_accounting_dir_path($timestamp));
	}


	/**
	 * @param  Integer if > 0 the pathname of the timestamps month is returned
	 * @return String  Relative path of accounting dir to storage dir for actual settlement run
	 */
	public static function get_relative_accounting_dir_path($timestamp = 0)
	{
		$time = $timestamp ? : strtotime('first day of last month');

		return 'data/billingbase/accounting/'.date('Y-m', $time);
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
	private function _init($sepa_accs, $salesmen)
	{
		$this->conf = BillingBase::first();

		// set language for this run
		\App::setLocale($this->conf->userlang);

		// create directory structure and remove old invoices
		if (is_dir(self::get_absolute_accounting_dir_path()))
			SettlementRunController::directory_cleanup(self::get_relative_accounting_dir_path());
		else
			mkdir(self::get_absolute_accounting_dir_path(), 0700, true);

		// Salesmen
		$prod_types = Product::getPossibleEnumValues('type');
		unset($prod_types['Credit']);

		foreach ($salesmen as $key => $sm)
		{
			$sm->all_prod_types = $prod_types;
			$sm->dir = self::get_relative_accounting_dir_path();
		}
		// directory to save file - is actually only needed for first salesmen
		// if (isset($salesmen[0])) $salesmen[0]->dir = $this->dir;


		// SepaAccount
		foreach ($sepa_accs as $acc)
		{
			$acc->dir = self::get_relative_accounting_dir_path();
			$acc->rcd = $this->conf->rcd ? date('Y-m-'.$this->conf->rcd) : date('Y-m-d', strtotime('+1 day'));
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

				$acc->invoice_nr = is_object($nr) ? $nr->invoice_nr : $acc->invoice_nr_start;
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
		echo "ZIP all Files\n";
		\Artisan::call('billing:zip');
	}


	/**
	 * Write Status to temporary file as buffer for settlement run status bar in GUI
	 *
	 * @param Integer
	 * @param String 	Note: is automatically translated to the appropriate language if string exists in lang/./messages.php
	 */
	public static function push_state($value, $message)
	{
		$arr = array(
			'message' => BaseViewController::translate_label($message),
			'value'   => round($value),
			);

		Storage::put('tmp/accCmdStatus', json_encode($arr));
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
	 *
	 * NOTE/TODO: 1000 Phonecalls need a bit more than 1 MB memory - if files get too large and we get memory
	 *  problems again, we should probably save calls to database and get them during command when needed
	 */
	private function _get_cdr_data()
	{
		$calls = [[]];

		\Artisan::call('billing:cdr');

		$filepaths = cdrCommand::get_cdr_pathnames();

		foreach ($filepaths as $provider => $filepath)
		{
			if (!is_file($filepath)) {
				Log::error('billing', "Missing call data record file from $provider");
				throw new Exception("Missing call data record file from $provider");
			}

			$calls += $this->{"_parse_$provider"."_csv"}($filepath);
		}

		return $calls;
	}



	/**
	 * Parse envia TEL CSV and Check if customerNr to Phonenr assignment exists
	 *
	 * @return array  [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_envia_csv($filepath)
	{
		Log::debug('billing', 'Parse envia TEL Call Data Records CSV');

		$csv = file($filepath);
		$calls = [[]];

		if (!$csv) {
			Log::error('billing', 'Empty envia call data record file');
			return $calls;
		}

		// skip first line of csv (column description)
		unset($csv[0]);
		$price = $count = 0;
		$unassigned = [];
		$customer_nrs = self::_get_customer_nrs();

		foreach ($csv as $line)
		{
			$arr = str_getcsv($line, ';');
			$customer_nr = intval(str_replace(['002-', '010-'], '', $arr[0]));

			$data = array(
				'calling_nr' => $arr[3],
				'date' 		=> substr($arr[4], 4).'-'.substr($arr[4], 2, 2).'-'.substr($arr[4], 0, 2),
				'starttime' => $arr[5],
				'duration' 	=> $arr[6],
				'called_nr' => $arr[7],
				'price' 	=> str_replace(',', '.', $arr[10])
				);

			if (in_array($customer_nr, $customer_nrs))
				$calls[$customer_nr][] = $data;
			else
			{
				// cumulate price of calls that can not be assigned to any contract
				if (!isset($unassigned[$arr[0]]))
					$unassigned[$arr[0]][$data['calling_nr']] = ['count' => 0, 'price' => 0];

				$unassigned[$arr[0]][$data['calling_nr']]['count'] += 1;
				$unassigned[$arr[0]][$data['calling_nr']]['price'] += $data['price'];
			}
		}

		$this->_log_unassigned_calls($unassigned);

		// warning when there are 5 times more customers then calls
		if ($calls && (count($customer_nrs) > 10 * count($calls)))
			Log::warning('billing', 'Very little data in enviatel call data record file ('.count($csv).' records). Possibly missing data!');

		return $calls;
	}


	/**
	 * Parse HLKomm CSV
	 *
	 * @return array 	[contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_hlkomm_csv($filepath)
	{
		$csv = file($filepath);

		if (!$csv) {
			Log::warning('billing', 'Empty hlkomm call data record file');
			return array(array());
		}

		// skip first 5 lines (descriptions)
		unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);

		$config = BillingBase::first();
		$unassigned = [];

		// get phonenr to contract_id listing - needed because only phonenr is mentioned in csv
		// BUG: Actually when a phonenumber is deleted on date 1.5. and then the same number is assigned to another contract, all
		// records of 1.4.-30.4. would be assigned to the new contract that actually hasn't done any call yet
		// As precaution we warn the user when he changes or creates a phonenumber so that this bug would be affected
		$phonenumbers_db = $this->_get_phonenumbers('sip.hlkomm.net');

        foreach ($phonenumbers_db as $value)
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

			$data = array(
				'calling_nr' => $phonenr1,
				'date' 		=> $line[0],
				'starttime' => $line[1],
				'duration' 	=> $line[10],
				'called_nr' => $phonenr2,
				'price' 	=> str_replace(',', '.', $line[13])
				);

			// calculate price with hlkomms distance zone
			// $a[5] = strpos($line[3], 'Mobilfunk national') !== false ? $a[5] * ($config->voip_extracharge_mobile_national / 100 + 1) : $a[5] * ($config->voip_extracharge_default / 100 + 1);
			$data['price'] = $line[15] == '990711' ? $data['price'] * ($config->voip_extracharge_mobile_national / 100 + 1) : $data['price'] * ($config->voip_extracharge_default / 100 + 1);

			if (isset($phonenrs[$phonenr1]))
				$calls[$phonenrs[$phonenr1]][] = $data;
			else if (isset($phonenrs[$phonenr2]))
				// our phonenr is the called nr - TODO: proof if this case can actually happen - normally this shouldnt be the case
				$calls[$phonenrs[$phonenr2]][] = $data;
			else {
				// there is a phonenr entry in csv that doesnt exist in our db - this case should never happen
				if (!isset($unassigned[$phonenr1]))
					$unassigned[$phonenr1] = ['count' => 0, 'price' => 0];

				$unassigned[$phonenr1]['count'] += 1;
				$unassigned[$phonenr1]['price'] += $data['price'];
			}

		}

		foreach ($unassigned as $pn => $arr) {
			$price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');
			Log::error('billing', trans('messages.cdr_missing_phonenr', ['phonenr' => $pn, 'count' => $arr['count'], 'price' => $price, 'currency' => $this->conf->currency]));
		}

		return $calls;
	}

	/**
	 * Parse PurTel CSV
	 *
	 * NOTE: Username to phonenumber combination must never change!
	 *
	 * @return array 	[contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
	 */
	protected function _parse_purtel_csv($filepath)
	{
		Log::debug('billing', 'Parse PurTel Call Data Records CSV');

		$csv   = file($filepath);
		$calls = [[]];

		if (!$csv) {
			Log::warning('billing', 'Empty envia call data record file');
			return $calls;
		}

		// skip first line of csv (column description)
		unset($csv[0]);

		$logged = $phonenumbers = $unassigned = [];
		$price = $count = 0;
		$customer_nrs = self::_get_customer_nrs();
		$registrar = 'deu3.purtel.com';
		$cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1+$this->conf->cdr_offset).' month'));

		// get phonenumbers because only username is given in CDR.csv
		$phonenumbers_db = \DB::table('phonenumber as p')
			->join('mta', 'p.mta_id', '=', 'mta.id')
			// \Modules\ProvVoip\Entities\Phonenumber::join('mta', 'phonenumber.mta_id', '=', 'mta.id')
			->join('modem', 'modem.id', '=', 'mta.modem_id')
			->join('contract as c', 'c.id', '=', 'modem.contract_id')
			->where(function ($query) use ($registrar) { $query
				->where('sipdomain', '=', $registrar)
				->orWhereNull('sipdomain')
				->orWhere('sipdomain', '=', '');})
			->select('modem.contract_id', 'c.number as contractnr', 'c.create_invoice', 'p.*')
			// ->withTrashed()
			->where(function ($query) use ($cdr_first_day_of_month) { $query
				->whereNull('p.deleted_at')
				->orWhere('p.deleted_at', '>=', $cdr_first_day_of_month);
				})
			->orderBy('p.deleted_at', 'asc')->orderBy('created_at', 'desc')
			->get();

		foreach ($phonenumbers_db as $p)
			$phonenumbers[$p->username] = $p->prefix_number.$p->number;

		foreach ($csv as $line)
		{
			$arr = str_getcsv($line, ';');

			// Discard Drebach Customers in a first step
			if (strpos($arr[7], '013-') !== false) {
				if (!in_array($arr[7], $logged))
					$logged[] = $arr[7];

				continue;
			}

			$customer_nr = intval(str_replace(['010-'], '', $arr[7]));
			$username 	 = $arr[2];
			$date = explode(' ', $arr[1]);

			if (!isset($phonenumbers[$username])) {
				Log::error('billing', "Phonenr of contract $customer_nr with username $username not found in DB. Calling number will not appear on invoice.");
				$phonenumbers[$username] = ' - ';
			}

			$data = array(
				'calling_nr' => $phonenumbers[$username],
				'date' 		=> $date[0],
				'starttime' => $date[1],
				'duration' 	=> gmdate("H:i:s", $arr[4]),
				'called_nr' => $arr[3],
				'price' 	=> $arr[10] / 100,
				);

			if (in_array($customer_nr, $customer_nrs))
				$calls[$customer_nr][] = $data;
			else
			{
				// cumulate price of calls that can not be assigned to any contract
				if (!isset($unassigned[$arr[7]]))
					$unassigned[$arr[7]][$data['calling_nr']] = ['count' => 0, 'price' => 0];

				$unassigned[$arr[7]][$data['calling_nr']]['count'] += 1;
				$unassigned[$arr[7]][$data['calling_nr']]['price'] += $data['price'];
			}
		}

		if ($logged)
			Log::notice('billing', "Purtel-CSV: Discard calls from customer numbers ".implode(', ', $logged)." (still km3 customer - from Drebach)");

		$this->_log_unassigned_calls($unassigned);

		// warning when there are approx 5 times more customers then calls
		if ($calls && (count($phonenumbers_db) > 5 * count($calls)))
			Log::warning('billing', 'Very little data in purtel call data record file ('.count($csv).' records). Possibly missing data!');

		return $calls;
	}


	/**
	 * Get list of all phonenumbers of all contracts belonging to a specific registrar
	 *
	 * @return Array
	 */
	private static function _get_customer_nrs()
	{
		$customer_nrs = [];

		$numbers = \DB::table('contract')->select(['id', 'number'])->whereNull('deleted_at')->get();

		foreach ($numbers as $num) {
			$customer_nrs[] = $num->id;
			$customer_nrs[] = $num->number;
		}

		return $customer_nrs;
	}


	private function _get_phonenumbers($registrar)
	{
		$cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1+$this->conf->cdr_offset).' month'));

		return \DB::table('phonenumber as p')
			->join('mta', 'p.mta_id', '=', 'mta.id')
			->join('modem', 'modem.id', '=', 'mta.modem_id')
			->join('contract as c', 'c.id', '=', 'modem.contract_id')
			->where(function ($query) use ($registrar) { $query
				->where('sipdomain', 'like', "%$registrar%")
				->orWhereNull('sipdomain')
				->orWhere('sipdomain', '=', '');})
			->where(function ($query) use ($cdr_first_day_of_month) { $query
				->whereNull('p.deleted_at')
				->orWhere('p.deleted_at', '>=', $cdr_first_day_of_month);
				})
			->select('modem.contract_id', 'c.number as contractnr', 'c.create_invoice', 'p.*')
			->orderBy('p.deleted_at', 'asc')->orderBy('p.created_at', 'desc')
			->get();
	}


	/**
	 * Log all cumulated prices of calls from specific phonenumbers that could not be assigned to any contract
	 *
	 * @param Array 	 [customer_id][phonenr] => [count, price]
	 */
	private function _log_unassigned_calls($unassigned)
	{
		foreach ($unassigned as $customer_nr => $pns)
		{
			foreach ($pns as $p => $arr)
			{
				$price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');

				Log::warning('billing', trans('messages.cdr_discarded_calls', array(
					'contractnr' => $customer_nr,
					'count' => $arr['count'],
					'phonenr' => $p,
					'price' => $price,
					'currency' => $this->conf->currency
					)));
			}
		}
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
