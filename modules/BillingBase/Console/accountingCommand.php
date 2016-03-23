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

class accountingCommand extends Command {

	/**
	 * The console command name, description, ...
	 *
	 * @var string
	 */
	protected $name 		= 'nms:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';

	protected $contract_validity = [
		'started_lastm' => false,		// for costs proportional to valid days of contract in this month
		'ratio'			=> 0,
		'expires'		=> false,		// if contract startet last month - used for contracts created after last run
	];

	// Array declaration for easy reordering of entries - see constructor!
	protected $records_arr = [
		'invoice_tariff' => [
			'Contractnr' => '',
			'Invoicenr' => '',
			'Target Month' => '',
			'Date' => '',
			'Cost Center' => '',
			'Count' => '',
			'Description' => '',
			'Product' => '',
			'Firstname' => '',
			'Lastname' => '',
			'Street' => '',
			'Zip' => '',
			'City' => '',
	], 'invoice_item' => [
		// == invoice tariff -> see constructor
	], 'booking' => [
			'Contractnr' => '',
			'Invoicenr' => '',
			'Date' => '',
			'RCD' => '',	// Requested Collection Date (Zahlungsziel)
			'Cost Center' => '',
			'Description' => '',
			'Net' => '',
			'Tax' => '',
			'Gross' => '',
			'Currency' => '',
			'Firstname' => '',
			'Lastname' => '',
			'Street' => '',
			'City' => '',
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
			'IBAN' => '',
			'BIC' => '',
			'MandateID' => '',
			'MandateDate' => ''
		]);

		parent::__construct();
	}

	/**
	 * Execute the console command
	 * Create invoice-, booking records and sepa xml file
	 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
	 */
	public function fire()
	{
		// instantiate logger for billing
		$logger = new Logger('Billing');
		$logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);
		$logger->addInfo(' #####	Starting Accounting Command	#####');


		// (offen needed) variables
		$now 		= date('Y-m-d');
		$month 		= date('Y-m');
		$last_month = date('Y-m-01', strtotime('now -1 months'));
		$this_month = date('Y-m-01');
		$next_month = date('Y-m-01', strtotime('now +1 months'));
		$m_in_sec = 60*60*24*30;	// month in seconds
		$sepa_dc = $sepa_dd = null;

		$records[0] = ['invoice_tariff' => '', 'invoice_item' => '', 'booking' => '', 'booking_sepa' => ''];

		dd($this->records_arr);

		// remove all entries of this month from accounting table if entries were already created (and create them new)
		$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this_month)->where('created_at', '<=', $next_month)->first();
		if (is_object($actually_created))
		{
			$logger->addNotice('Table was already created this month - will be recreated now!');
			DB::update('DELETE FROM '.$this->tablename.' WHERE created_at>='.$this_month);
		}

		// check date of last run and get last invoice nr - all item entries after this date have to be included to the current billing cycle
		$last_run = DB::table($this->tablename)->orderBy('created_at', 'desc')->select('created_at', 'invoice_nr')->first();
		if (is_object($last_run))
		{
			$last_run = $last_run->created_at;
			$invoice_nr = $last_run->invoice_nr;
		}
		else
		{
			// first run for this system
			$last_run = date('1970-01-01');
			$invoice_nr = 999999;
		}
		$logger->addDebug('Last creation of table was on '.$last_run);
		
		
		/*
		 * Loop over all Contracts - Log all out of date contracts - consider starting & expiration dates for cost adaption
		 */
		$cs = Contract::all();
		$prices = Product::all();
		$sepa_accounts = SepaAccount::all();

		foreach ($cs as $c)
		{
			// check validity of contract and get important flags
			if ($this->contract_validity = $c->get_contract_validity_arr($lust_run) == null)
			{
				$logger->addNotice('Contract '.$c->id.' is out of date');
				continue;				
			}

			// variable resets or incrementations
			$invoice_nr += 1;
			$charge = []; 						// total costs for this month for current contract


			/*
			 * Add internet, voip and tv tariffs
			 */
			$tariffs = null;
			$tariffs['inet'] = $prices->find($c->price_id);
			$tariffs['voip'] = $prices->find($c->voip_price_id);
			$tariffs['tv'] 	 = $prices->find($c->tv_price_id);

			foreach ($tariffs as $t)
			{
				if ($t)
				{
					$price = $t->price;
					if ($ratio)
						$price = round($price * $ratio, 2);
					if ($started_lastm && $ratio != 0)
						$price = round((1 + $ratio)*$t->price, 2);
					
					// TODO: consider starting point - directly or after 1 year after contract begin
					if ($t->billing_cycle == 'Yearly')
					{
						$price = 0;
						if (date('m') == date('m', strtotime($c->created_at)))
							$price = $t->price;
					} 

					// choose different costcenter if it is assigned to the tariff
					$acc_id = $c->costcenter->sepa_account->id;
					if ($t->costcenter_id)
						$acc_id = $t->costcenter->id;

					// write to accounting table
					DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$t->name.'", '.$price.', NOW(), '.$invoice_nr.')');

					// add invoice record
					$records[$acc_id]['invoice_tariff'][] = $this->get_invoice_record($c, $t, $invoice_nr, $price, null);
					$charge[$acc_id] = $price;
				}
			}

			/*
			 * add monthly item costs for following items:
				* monthly
				* once: created within last billing period | actual run is within valid_from and valid_to date
				* yearly
			 * calculate total sum of items considering contract starting & expiration date
			 */
			$items = $c->items;
			foreach ($items as $item)
			{
				$price_entry = $item->price;
				$entry_cost = 0;
				$text 		= '';

				switch($price_entry->billing_cycle)
				{
					case 'Monthly':
						$entry_cost = $price_entry->price;
						if ($ratio)
							$entry_cost = round($price_entry->price * $ratio, 2);
						if ($started_lastm)
							$entry_cost = round((1 + $ratio)*$price_entry->price, 2);						
						break;
					case 'Once':
						if ($item->created_at > $last_run && $item->valid_to == '0000-00-00')
							$entry_cost = $price_entry->price;
						if ($item->valid_to != '0000-00-00')
						{
							if ($item->valid_to >= $now && $item->valid_from <= $now)
							{
								// calculate total range - note: consider last-run here
								$total_months = round((strtotime($item->valid_to) - strtotime($item->valid_from)) / $m_in_sec) + 1;
								if (($create_d = date('Y-m-01', strtotime($item->created_at))) == $last_month && $last_run < $create_d)
									$total_months -= 1;
								$entry_cost = $price_entry->price / $total_months;
								// $part = totm - (to - this)
								$part = round((($total_months)*$m_in_sec + strtotime($this_month) - strtotime($item->valid_to))/$m_in_sec);
								$text = " | part $part/$total_months";

								// items with valid_to in future, but contract expires
								if ($expires)
								{
									$entry_cost = ($total_months - $part + 1) * $price_entry->price;
									$text = " | last $part parts of $total_months";
								}
							}
						}

						break;
					case 'Yearly':
						// TODO: consider starting point - directly or after 1 year after creating
						if (date('m', strtotime($item->created_at)) == date('m'))
							$entry_cost = $price_entry->price;
							// strtotime("+1 year", strtotime($item->created_at))) == $month)
						break;
				}
				
				// use credit amount only on credits
				if (strtolower($item->price->name) == 'credit')
				{
					if ($item->credit_amount)
						$entry_cost = $item->credit_amount;
					if ($entry_cost > 0)
						$entry_cost *= -1;
				}
				$count = 1;
				if ($item->count)
					$count = $item->count;

				$acc_id = $c->costcenter->sepa_account->id;
				if ($price_entry->costcenter_id)
						$acc_id = $price_entry->costcenter->id;

				// add accounting table entry
				DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, count, created_at, invoice_nr) VALUES(".$c->id.', "'.$price_entry->name.$text.'", '.$entry_cost.', '.$count.', NOW(), '.$invoice_nr.')');
				// add invoice record
				$records[$acc_id]['invoice_item'] [] = $this->get_invoice_record($c, $price_entry, $invoice_nr, $entry_cost, $text);
				$charge[$acc_id] = isset($charge[$acc_id]) ? $charge[$acc_id] + $entry_cost * $count : $entry_cost * $count;

			} // end foreach

			/*
			 * Check if valid mandate exists, add sepa data to ordered structure, log all out of date contracts
			 */
			$mandate = null;
			$mandates = $c->sepamandates->all();
			if (!isset($mandates[0]))
				goto cont;

			foreach ($mandates as $m)
			{
				if ($m->sepa_valid_from <= $now && ($m->sepa_valid_to == '0000-00-00' || $m->sepa_valid_to > $now))
				{
					$mandate = $m;
					break;
				}
			}
cont:
			if (!$mandate)
			{
				$logger->addNotice('Contract '.$c->id.' has no valid sepa mandate');

				foreach ($charge as $acc_id => $value)
					$records[$acc_id]['booking'][] = $this->get_booking_record($c, null, $invoice_nr, $now, $started_lastm, $value);

				continue;
			}

			foreach ($charge as $acc_id => $value) 
				$records[$acc_id]['booking_sepa'][] = $this->get_booking_record($c, $mandate, $invoice_nr, $started_lastm, $value);

			/*
			 * Create ordered structure for sepa file creation - Note: Charge == 0 is automatically excluded
			 */
			$t = PaymentInformation::S_RECURRING;
			if (date('Y-m', strtotime($c->contract_start)) == $month && !$mandate->recurring)
				$t = PaymentInformation::S_FIRST;
			else if (date('Y-m', strtotime($c->contract_end)) == $month)
				$t = PaymentInformation::S_FINAL;

			
			foreach ($charge as $acc_id => $value)
			{
				$xml_entry = ['mandate' => $mandate, 'charge' => $value, 'invoice_nr' => $invoice_nr, 'started_lastm' => $started_lastm];
				
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

		foreach ($records as $acc_id => $acc_records)
		{
			if ($acc_id == 0)
				continue;

			dd($sepa_dd, $sepa_accounts->find(1), $sepa_accounts->find(2));

			$this->create_sepa_xml($sepa_dd[$acc_id], $sepa_dc[$acc_id], $sepa_accounts->find($acc_id));

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



	protected function get_invoice_record($contract, $item, $invoice_nr, $price, $text = '')
	{
		$arr = $this->records_arr['invoice_tariff'];

		$arr['Contractnr'] 	= $contract->id;
		$arr['Invoicenr'] 	= $invoice_nr;
		$arr['Target Month'] = date('m');
		$arr['Date'] 		= date('Y-m-d');
		$arr['Cost Center'] = isset($contract->costcenter->name) ? $contract->costcenter->name : '';
		$arr['Count'] 		= '1';
		$arr['Description'] = $item->name.$text;
		$arr['Product'] 		= $price;
		$arr['Firstname'] 	= $contract->firstname;
		$arr['Lastname'] 	= $contract->lastname;
		$arr['Street'] 		= $contract->street;
		$arr['Zip'] 		= $contract->zip;
		$arr['City'] 		= $contract->city;

		return implode("\t", $arr)."\n";
	}

	protected function get_booking_record($contract, $mandate, $invoice_nr, $started_lastm, $charge)
	{

		$arr = $this->records_arr['booking'];
		if ($mandate)
			$arr = $this->records_arr['booking_sepa'];

		// TODO: use requested collection date (Zahlungsziel), currency & tax from global config
		$rcd = date('Y-m-d', strtotime('now +6 days'));
		$cur = 'EUR';
		$tax = 0.19;
		$txt = '';
		if ($started_lastm)
			$txt = date('m', strtotime('now -1 Months')).'+';

		$arr['Contractnr'] 	= $contract->id;
		$arr['Invoicenr'] 	= $invoice_nr;
		$arr['Date'] 		= date('Y-m-d');
		$arr['RCD'] 		= $rcd;
		$arr['Cost Center'] = isset($contract->costcenter->name) ? $contract->costcenter->name : '';
		$arr['Description'] = 'Month '.$txt.date('m/Y');
		$arr['Net'] 		= round($charge * (1-$tax), 2);
		$arr['Tax'] 		= round($charge * $tax, 2);
		$arr['Gross'] 		= $charge;
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
		$sepa_dd_xml_file 	= storage_path('billing/sepa_dd.xml');
		$sepa_dc_xml_file 	= storage_path('billing/sepa_dc.xml');
		$msg_id = date('YmdHis').$acc->id;		// km3 uses actual time
		$creditor['name'] = $acc->name;
		$creditor['iban'] = $acc->iban;
		$creditor['bic']  = $acc->bic;
		$creditor['id']   = $acc->creditorid;

var_dump($msg_id);

		// Set the initial information for direct debits
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $creditor['name']);

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
				if ($r['started_lastm'])
					$info .= ' + '.date('m/Y', strtotime('now -1 months'));
				
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

		foreach($sepa_dc as $r)
		{
			$info = 'Month '.date('m/Y');
			if ($r['started_lastm'])
				$info .= ' + '.date('m/Y', strtotime('now -1 months'));
			
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
			// ['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}