<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Contract;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use File;
use DB;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\BillingBase;

class accountingCommand extends Command {

	/**
	 * The console command & table name, description, data arrays
	 *
	 * @var string
	 */
	protected $name 		= 'nms:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';
	
	protected $logger;					// billing logger instance for this command - billing
	protected $dates;					// offen needed time strings for faster access - see constructor


	// Array declaration for easy reordering of entries - see constructor!
	protected $records_arr = [
		'invoice_tariff' => [
			'Contractnr' 	=> '',
			'Invoicenr' 	=> '',
			'Target Month'  => '',
			'Date' 			=> '',
			'Cost Center' 	=> '',
			'Count' 		=> '',
			'Description' 	=> '',
			'Price' 		=> '',
			'Firstname' 	=> '',
			'Lastname' 		=> '',
			'Street' 		=> '',
			'Zip' 			=> '',
			'City' 			=> '',
	], 'invoice_item' => [
		// == invoice tariff -> see constructor
	], 'booking' => [
			'Contractnr'	=> '',
			'Invoicenr'		=> '',
			'Date' 			=> '',
			'RCD' 			=> '',	// Requested Collection Date (Zahlungsziel)
			'Cost Center' 	=> '',
			'Description' 	=> '',
			'Net' 			=> '',
			'Tax' 			=> '',
			'Gross' 		=> '',
			'Currency' 		=> '',
			'Firstname' 	=> '',
			'Lastname' 		=> '',
			'Street' 		=> '',
			'Zip'			=> '',
			'City' 			=> '',
	], 'booking_sepa' => [
		// see constructor
		]];

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->records_arr['invoice_item'] = $this->records_arr['invoice_tariff'];
		$this->records_arr['booking_sepa'] = array_merge($this->records_arr['booking'], [
			'Account Holder' => '',
			'IBAN' 			=> '',
			'BIC' 			=> '',
			'MandateID' 	=> '',
			'MandateDate' 	=> ''
		]);

		// instantiate this->logger for billing
		$this->logger = new Logger('Billing');
		$this->logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);

		$this->dates = array(
			'today' 		=> date('Y-m-d'),
			'm' 			=> date('m'),
			'Y' 			=> date('Y'),
			'this_m'	 	=> date('Y-m'),
			'this_m_bill'	=> date('m/Y'),
			'last_m'		=> date('m', strtotime("first day of last month")),			// written this way because of known bug
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
		$this->logger->addInfo(' #####    Starting Accounting Command    #####');

		// remove all entries of this month from accounting table if entries were already created (and create them new), but not for walk with argument=2
		if ($this->argument('cycle') != 2)
		{
			$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this->dates['thism_01'])->where('created_at', '<=', $this->dates['nextm_01'])->first();
			if (is_object($actually_created))
			{
				$this->logger->addNotice('Table was already created this month - will be recreated now!');
				DB::update('DELETE FROM '.$this->tablename.' WHERE created_at>='.$this->dates['thism_01']);
			}
		}

		// check date of last run and get last invoice nr - all item entries after this date have to be included to the current billing cycle
		$last_run = DB::table($this->tablename)->orderBy('created_at', 'desc')->select('created_at', 'invoice_nr')->first();
		if (is_object($last_run))
		{
			$this->dates['last_run'] = $last_run->created_at;
			$invoice_nr = $last_run->invoice_nr;
		}
		else
		{
			// first run for this system
			$this->dates['last_run'] = $this->dates['null'];
			$invoice_nr = 999999;
		}
		$this->logger->addDebug('Last run was on '.$this->dates['last_run']);
		
		$conf = BillingBase::first();
		$sepa_dd = $sepa_dc = [];
		
		/*
		 * Loop over all Contracts
		 */
		foreach (Contract::all() as $c)
		{
			// check validity of contract
			if (!$this->check_validity($c->contract_start, $c->contract_end))
			{
				$this->logger->addNotice('Contract '.$c->id.' is out of date');
				continue;				
			}

			if (!$c->create_invoice)
				continue;

			// variable resets or incrementations
			$invoice_nr += 1;
			$charge 	= []; 					// total costs for this month for current contract
			$expires	= false;

			if (date('Y-m', strtotime($c->contract_end)) == $this->dates['this_m'])
				$expires = true;


			/*
			 * Add internet, voip and tv tariffs and all other items and calculate price for this month considering 
			 * contract starting & expiration date, calculate total sum of items for booking records
			 */
			foreach ($c->items as $item)
			{
				// check validity
				$start = ($item->valid_from == null || $item->valid_from == $this->dates['null']) ? $item->created_at : $item->valid_from;
				if (is_object($start))
					$start = $start->toDateString();

				if (!$this->check_validity($start, $item->valid_to))
					continue;

				// only TV items for this walk (when argument=2)
				if ($this->argument('cycle') == 2 && $item->product->type != 'TV')
					continue;
				if ($this->argument('cycle') == 1 && $item->product->type == 'TV')
					continue;


				$costcenter = $item->product->costcenter ? $item->product->costcenter : $c->costcenter;

				$ret = $item->calculate_price_and_span($this->dates, $costcenter, $expires);
				
				$price = $ret['price'];
				if (!$price)
					continue;
				$text  = $ret['text'];

				// write to accounting table
				$count = $item->count ? $item->count : 1;
				DB::update('INSERT INTO '.$this->tablename.' (created_at, contract_id, name, product_id, ratio, count, invoice_nr) VALUES(NOW(),'.$c->id.',"'.$item->name.'",'.$item->product->id.','.$ret['ratio'].','.$count.','.$invoice_nr.')');

				// get account via costcenter
				$acc_id = $costcenter->sepa_account_id;

				// increase charge for account += $price
				$charge[$acc_id] = isset($charge[$acc_id]) ? $charge[$acc_id] + $price : $price;

				// write to accounting records of account
				switch ($item->product->type)
				{
					case 'Internet':
					case 'TV':
					case 'Voip':
						$rec_arr = 'invoice_tariff'; break;
					default:
						$rec_arr = 'invoice_item'; break;
				}


				$records[$acc_id][$rec_arr][] = $this->get_invoice_record($item, $price, $invoice_nr, $text);

			} // end of item loop

			// Check if valid mandate exists, add sepa data to ordered structure, log all out of date contracts
			$mandate = null;
			$mandates = $c->sepamandates->all();
			if (!$mandates)
				goto cont;

			foreach ($mandates as $m)
			{
				if ($m->sepa_valid_from <= $this->dates['today'] && ($m->sepa_valid_to == '0000-00-00' || $m->sepa_valid_to > $this->dates['today']))
				{
					$mandate = $m;
					break;
				}
			}
cont:
			if (!$mandate)
			{
				$this->logger->addNotice('Contract '.$c->id.' has no valid sepa mandate');
				$rec_arr = 'booking';
			}
			else
				$rec_arr = 'booking_sepa';

			// write to booking records of account with total charge
			foreach ($charge as $acc_id => $value) 
				$records[$acc_id][$rec_arr][] = $this->get_booking_record($c, $mandate, $invoice_nr, $value, $conf);

			if (!$mandate)
				continue;

			// Create ordered structure for sepa file creation - Note: Charge == 0 is automatically excluded
			$t = PaymentInformation::S_RECURRING;
			if (date('Y-m', strtotime($c->contract_start)) == $this->dates['m'] && !$mandate->recurring)
				$t = PaymentInformation::S_FIRST;
			else if (date('Y-m', strtotime($c->contract_end)) == $this->dates['m'])
				$t = PaymentInformation::S_FINAL;
			
			foreach ($charge as $acc_id => $value)
			{
				// $xml_entry = ['mandate' => $mandate, 'charge' => $value, 'invoice_nr' => $invoice_nr, 'started_lastm' => $started_lastm];
				$xml_entry = ['mandate' => $mandate, 'charge' => $value, 'invoice_nr' => $invoice_nr];
				
				if ($value < 0)
					$sepa_dc[$acc_id][] = $xml_entry;
				else
					$sepa_dd[$acc_id][$t][] = $xml_entry;					
			}


		} // end of loop over contracts

		/*
		 * Store SEPA & Billing Files (foreach type and foreach account)
		 */
		if (!is_dir(storage_path('billing')))
			mkdir(storage_path('billing'));

		$sepa_accounts = SepaAccount::all();

		foreach ($records as $acc_id => $acc_records)
		{
			if ($acc_id == 0)
				continue;

			$sepa_dd = isset($sepa_dd[$acc_id]) ? $sepa_dd[$acc_id] : null;
			$sepa_dc = isset($sepa_dc[$acc_id]) ? $sepa_dc[$acc_id] : null;
			
			$this->create_sepa_xml($sepa_dd, $sepa_dc, $sepa_accounts->find($acc_id));

			foreach ($acc_records as $type => $entries)
			{
				$file = storage_path("billing/$type"."_records_".$sepa_accounts->find($acc_id)->name.".txt");
				// initialise record files with Column names as first line
				File::put($file, implode("\t", array_keys($this->records_arr[$type]))."\n");
				File::append($file, implode($entries));
				echo "stored $type records in $file\n";
			}
		
		}


	}

	/**
	 * Cross checks start and end dates against actual day
	 */
	protected function check_validity($start, $end)
	{
		return ($start > $this->dates['today'] || ($end != $this->dates['null'] && $end < $this->dates['today'])) ? false : true;
	}



	protected function get_invoice_record($item, $price, $invoice_nr, $text = '')
	{
		$arr = $this->records_arr['invoice_tariff'];

		$arr['Contractnr'] 	= $item->contract->id;
		$arr['Invoicenr'] 	= $invoice_nr;
		$arr['Target Month'] = date('m');
		$arr['Date'] 		= date('Y-m-d');
		$arr['Cost Center'] = isset($item->contract->costcenter->name) ? $item->contract->costcenter->name : '';
		$arr['Count'] 		= $item->count ? $item_count : '1';
		$arr['Description'] = $item->name.$text;
		$arr['Price'] 		= $price;
		$arr['Firstname'] 	= $item->contract->firstname;
		$arr['Lastname'] 	= $item->contract->lastname;
		$arr['Street'] 		= $item->contract->street;
		$arr['Zip'] 		= $item->contract->zip;
		$arr['City'] 		= $item->contract->city;

		return implode("\t", $arr)."\n";
	}

	protected function get_booking_record($contract, $mandate, $invoice_nr, /* $started_lastm,*/ $charge, $conf)
	{

		$arr = $this->records_arr['booking'];
		if ($mandate)
			$arr = $this->records_arr['booking_sepa'];

		// use requested collection date (Zahlungsziel), currency & tax from global config
		$rcd = $conf->rcd ? $conf->rcd : date('Y-m-d', strtotime('+6 days'));
		$cur = $conf->currency ? $conf->currency : 'EUR';
		$tax = $conf->tax ? $conf->tax / 100 : 0.19;
		// $txt = '';
		// if ($started_lastm)
		// 	$txt = date('m', strtotime('-1 month')).'+';

		$arr['Contractnr'] 	= $contract->id;
		$arr['Invoicenr'] 	= $invoice_nr;
		$arr['Date'] 		= date('Y-m-d');
		$arr['RCD'] 		= $rcd;
		$arr['Cost Center'] = isset($contract->costcenter->name) ? $contract->costcenter->name : '';
		$arr['Description'] = 'Month '.date('m/Y');
		$arr['Net'] 		= round($charge * (1-$tax), 2);
		$arr['Tax'] 		= round($charge * $tax, 2);
		$arr['Gross'] 		= round($charge, 2);
		$arr['Currency'] 	= $cur;
		$arr['Firstname'] 	= $contract->firstname;
		$arr['Lastname'] 	= $contract->lastname;
		$arr['Street'] 		= $contract->street;
		$arr['Zip'] 		= $contract->zip;
		$arr['City'] 		= $contract->city;
		if ($mandate)
		{
			$arr['Account Holder'] 	= $mandate->sepa_holder;
			$arr['IBAN']			= $mandate->sepa_iban;
			$arr['BIC'] 			= $mandate->sepa_bic;
			$arr['MandateID'] 		= $mandate->reference;
			$arr['MandateDate']		= $mandate->signature_date;
		}

		return implode("\t", $arr)."\n";
	}

	/**
	 * Create SEPA XML
	 * @param $sepa_dd - sepa transfer information array for direct debits
	 * @param $sepa_dc - sepa transfer information array for direct credits
	 * @author Nino Ryschawy
	 */
	protected function create_sepa_xml($sepa_dd, $sepa_dc = null, $acc)
	{
		$sepa_dd_xml_file 	= storage_path('billing/sepa_dd_').$acc->name.'.xml';
		$sepa_dc_xml_file 	= storage_path('billing/sepa_dc_').$acc->name.'.xml';
		$msg_id = date('YmdHis').$acc->id;		// km3 uses actual time
		$creditor['name'] = $acc->name;
		$creditor['iban'] = $acc->iban;
		$creditor['bic']  = $acc->bic;
		$creditor['id']   = $acc->creditorid;


		// Set the initial information for direct debits
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $creditor['name']);

		if (!$sepa_dd)
			goto sepa_dc;

		foreach ($sepa_dd as $type => $records)
		{
			// create a payment
			$directDebit->addPaymentInfo($msg_id, array(
				'id'                    => $msg_id,
				'creditorName'          => $creditor['name'],
				'creditorAccountIBAN'   => $creditor['iban'],
				'creditorAgentBIC'      => $creditor['bic'],
				'seqType'               => $type,
				'creditorId'            => $creditor['id']
				// 'dueDate'				=> // requested collection date (Fälligkeits-/Ausführungsdatum) - from global config
			));

			foreach($records as $r)
			{
				$payment_id = 'RG '.$r['invoice_nr'];
				$info 		= 'Month '.date('m/Y');
				// if ($r['started_lastm'])
				// 	$info .= ' + '.date('m/Y', strtotime('now -1 months'));
				
				// Add a Single Transaction to the named payment
				$directDebit->addTransfer($msg_id, array(
					'endToEndId'			=> $payment_id,
					'amount'                => $r['charge'],
					'debtorIban'            => $r['mandate']->sepa_iban,
					'debtorBic'             => $r['mandate']->sepa_bic,
					'debtorName'            => $r['mandate']->sepa_holder,
					'debtorMandate'         => $r['mandate']->reference,
					'debtorMandateSignDate' => $r['mandate']->signature_date,
					'remittanceInformation' => $info,
				));
			}
		}

		// Retrieve the resulting XML
		File::put($sepa_dd_xml_file, $directDebit->asXML());
		echo "stored sepa direct debit xml in $sepa_dd_xml_file\n";


		// Set the initial information for direct credits
		if (!$sepa_dc)
			return;
		$customerCredit = TransferFileFacadeFactory::createCustomerCredit($msg_id.'C', $creditor['name']);

		$customerCredit->addPaymentInfo($msg_id.'C', array(
			'id'                      => $msg_id.'C',
			'debtorName'              => $creditor['name'],
			'debtorAccountIBAN'       => $creditor['iban'],
			'debtorAgentBIC'          => $creditor['bic'],
		));

sepa_dc:

		if (!$sepa_dc)
			return;

		foreach($sepa_dc as $r)
		{
			$info = 'Month '.date('m/Y');
			// if ($r['started_lastm'])
				// $info .= ' + '.date('m/Y', strtotime('now -1 months'));
			
			// Add a Single Transaction to the named payment
			$customerCredit->addTransfer($msg_id.'C', array(
				'amount'                  => $r['charge']*(-1),
				'creditorIban'            => $r['mandate']->sepa_iban,
				'creditorBic'             => $r['mandate']->sepa_bic,
				'creditorName'            => $r['mandate']->sepa_holder,
				'remittanceInformation'   => $info
			));
		}

		// Retrieve the resulting XML
		File::put($sepa_dc_xml_file, $customerCredit->asXML());
		echo "stored sepa direct credit xml in $sepa_dc_xml_file\n";

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