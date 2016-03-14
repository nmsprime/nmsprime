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
use Modules\BillingBase\Entities\Price;

class accountingCommand extends Command {

	/**
	 * The console command name, description, ...
	 *
	 * @var string
	 */
	protected $name 		= 'nms:accounting';
	protected $tablename 	= 'accounting';
	protected $description 	= 'Create accounting records table, Direct Debit XML, invoice and transaction list from contracts and related items';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command
	 * Create Buchungssatz.txt -> total cost per month per contract
	 	* Date , Company, Firstname, Lastname, Adress, Payment_target, Netto, Mwst, Brutto, Currency, BILLINGNR, Sepa-Data-Creditor (ISP), 
	 *		Clientnr/Reference, Bookingnr, Traffic?, ContractID, Kostenstelle, Sepa-Data-Debitor (IBAN,BIC,MandateID,MandateDate)
	 * Create Rechnungssatz.txt -> accounting records
	 	* (for Month), Date, Billingnr, Itemname, Cost, Clientnr, Clientadr
	 * Create Sepa-XML
	 *
	 * @return mixed
	 */
	public function fire()
	{
		/*
		 * TODO: add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
		 * add every new accounting record to the table for every contract once in a month
		 */

		$logger = new Logger('Billing');
		$logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);
		$logger->addInfo(' #####	Creating Accounting Record table 	#####');


		// (offen needed) variables
		$now 		= date('Y-m-d');
		$month 		= date('Y-m');
		$last_month = date('Y-m-01', strtotime('now -1 months'));
		$this_month = date('Y-m-01');
		$next_month = date('Y-m-01', strtotime('now +1 months'));
		$m_in_sec = 60*60*24*30;	// month in seconds
		$booking_records_file 	= storage_path('billing/booking_records.txt');
		$invoice_records_file	= storage_path('billing/invoice_records.txt');
		$sepa_xml_file 			= storage_path('billing/sepa.xml');
		$booking_records = '';
		$invoice_records = '';


		// create Files
		if (!is_dir(storage_path('billing')))
			mkdir(storage_path('billing'));
		// hash always zero when price is 0,00 !?!? , RCD = Requested Collection Date (Zahlungsziel), Net=netto, gross=brutto
		// TODO: differentiate between net and gross (before tax) prices
		File::put($invoice_records_file, "for Month\tDate\tInvoice Nr\tDescription\tPrice\tContractnr\tFirstname\tLastname\tStreet\tCity\tCost Center\tHash\n");
		File::put($booking_records_file, "Date\tCompany\tFirstname\tLastname\tStreet\tPostal Code\tCity\tStreet\tCity\tRCD\tNet\tSales tax (VAT)\tGross\tCurrency\tInvoicenr\tInstitute\tAccount Holder\tContractnr\tCost Center\tHash\tIBAN\tBIC\tMandateId\tMandateDate\n");


		// remove all entries of this month (if entries were already created) and create them new
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
			$last_run = date('1970-01-01');
			$invoice_nr = 999999;
		}
		$logger->addDebug('Last creation of table was on '.$last_run);
		
		
		/*
		 * Loop over all Contracts - Log all out of date contracts - consider starting & expiration dates for cost adaption
		 */
		$cs = Contract::all();
		foreach ($cs as $c)
		{
			// contract is out of date
			if ($c->contract_start >= $now || ($c->contract_end != '0000-00-00' && $c->contract_end < $now))
			{
				$logger->addNotice('Contract '.$c->id.' is out of date');
				continue;
			}


			// variable resets or incrementations
			$invoice_nr += 1;
			$charge = 0;				// total costs for this month for current contract
			$ratio = null;
			$expires = false;
			$started_lastm = false;

			// contract starts this month
			if (date('Y-m', strtotime($c->contract_start)) == $month)
			{
				$days_remaining = date('t') - date('d', strtotime($c->contract_start));
				$logger->addDebug('Contract '.$c->id." starts this month - billing for $days_remaining days");
				$ratio = $days_remaining/date('t');
			}
			// contract was created last month after last_run
			if (date('Y-m-01', strtotime($c->contract_start)) == $last_month && strtotime($c->contract_start) > strtotime($last_run))
			{
				$days_remaining = date('t', strtotime($last_month)) - date('d', strtotime($c->contract_start));
				$logger->addDebug('Contract '.$c->id." was starting last month - billing for additional $days_remaining days");
				$ratio = $days_remaining/date('t', strtotime($last_month));
				$started_lastm = true;
			}
			// contract expires this month - contract ends always on end of month - shall be dynamic???
			if (date('Y-m', strtotime($c->contract_end)) == $month)
			{
				$days_remaining = date('d', strtotime($c->contract_end)) - date('d', strtotime($this_month));
				// $ratio = $days_remaining/date('t');
				$logger->addDebug('Contract '.$c->id." expires this month - billing for $days_remaining days");
				$expires = true;
			}


			// add internet and voip tariffs, TODO?: choose voip tariff dependent on its name?
			$tariff = null;
			$tariff['inet'] = Price::find($c->price_id);
			if ($c->voip_tariff != '')
				$tariff['voip'] = Price::where('voip_tariff', '=', $c->voip_tariff)->get()->all()[0];

			foreach ($tariff as $t)
			{
				if ($t)
				{
					$price = $t->price;
					if (isset($ratio))
						$price = round($t->price * $ratio, 2);
					if ($started_lastm)
						$price = round((1 + $ratio)*$t->price, 2);

					// var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$t->name.'", '.$price.", NOW(), '.$invoice_nr.')');
					// accounting table
					DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$t->name.'", '.$price.', NOW(), '.$invoice_nr.')');
					// invoice records, TODO: add hash
					$invoice_records .= date('m')."\t$now\t$invoice_nr\t".$t->name."\t$price\t".$c->id."\t".$c->firstname."\t".$c->lastname."\t".$c->street."\t".$c->city."\t".$c->cost_center."\n";

					$charge += $price;
				}
			}

			/*
			 * add monthly item costs for following items:
			 	* monthly
			 	* once: created within last billing period | actual run is within payment_from and payment_to date
			 	* yearly
			 * calculate total sum of items considering contract starting & expiration date
			 */
			$items = $c->items;
			foreach ($items as $item)
			{
				$price_entry = Price::find($item->price_id);
				$entry_cost = 0;
				$text = '';
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
						if ($item->created_at > $last_run && $item->payment_to == '0000-00-00')
							$entry_cost = $price_entry->price;
						if ($item->payment_to != '0000-00-00')
						{
							if ($item->payment_to >= $now && $item->payment_from <= $now)
							{
								// calculate total range - note: consider last-run here
								$total_months = round((strtotime($item->payment_to) - strtotime($item->payment_from)) / $m_in_sec) + 1;
								if (($create_d = date('Y-m-01', strtotime($item->created_at))) == $last_month && $last_run < $create_d)
									$total_months -= 1;
								$entry_cost = $price_entry->price / $total_months;
								// $part = totm - (to - this)
								$part = round((($total_months)*$m_in_sec + strtotime($this_month) - strtotime($item->payment_to))/$m_in_sec);
								$text = " | part $part/$total_months";

								// items with payment_to in future, but contract expires
								if ($expires)
								{
									$entry_cost = ($total_months - $part + 1) * $price_entry->price;
									$text = " | last $part parts of $total_months";
								}

								// dd($total_months, $item);
							}
						}

						break;
					case 'Yearly':
						if (date('Y-m', strtotime("+1 year", strtotime($item->created_at))) == $month)
							$entry_cost = $price_entry->price;
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

				// var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$price_entry->name.$text.'", '.$entry_cost.', NOW(), '.$invoice_nr.')');
				// accounting table
				DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$price_entry->name.$text.'", '.$entry_cost.', NOW(), '.$invoice_nr.')');
				// invoice records, TODO: add hash
				$invoice_records .= date('m')."\t$now\t$invoice_nr\t".$t->name."\t$price\t".$c->id."\t".$c->firstname."\t".$c->lastname."\t".$c->street."\t".$c->city."\t".$c->cost_center."\n";

				$charge += $entry_cost;
			}


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
				goto end_of_loop;
			}


			// Create ordered structure for sepa file creation
			$t = PaymentInformation::S_RECURRING;
			if (date('Y-m', strtotime($c->contract_start)) == $month && !$mandate->recurring)
				$t = PaymentInformation::S_FIRST;
			else if (date('Y-m', strtotime($c->contract_end)) == $month)
				$t = PaymentInformation::S_FINAL;

			$sepa_tx[$t][] = ['mandate' => $mandate, 'charge' => $charge, 'invoice_nr' => $invoice_nr, 'started_lastm' => $started_lastm];

end_of_loop:

			// File::put($booking_records_file, "Date\tCompany\tFirstname\tLastname\tStreet\tPostal Code\tCity\tStreet\tCity\RCD\tNet\tsalex tax (VAT)\tGross\tCurrency\tInvoicenr\tInstitute\tAccount Holder\tContractnr\tCost Center\tHash\tIBAN\tBIC\tMandateId\tMandateDate");
			// TODO: use requested collection date (Zahlungsziel), currency from global config, calculate tax
			$rcd = date('Y-m-d', strtotime('now +6 days'));
			$tax = 0;
			$currency = 'EUR';
			$institute = $account_holder = $iban = $bic = $sepa_date = $sepa_id = '';
			if ($mandate)
			{
				$institute 		= $mandate->sepa_institute;
				$account_holder = $mandate->sepa_holder;
				$iban 			= $mandate->sepa_iban;
				$bic 			= $mandate->sepa_bic;
				$sepa_date 		= $mandate->signature_date;
				$sepa_id 		= $mandate->reference;
			}
			$hash = null;
			$booking_records .= "$now\t".$c->company."\t".$c->firstname."\t".$c->lastname."\t".$c->street."\t".$c->zip."\t".$c->city."\t$rcd\t$charge\t$tax\t$currency\t$invoice_nr\t$institute\t$account_holder\t".$c->id."\t".$c->cost_center."\t".$hash."\t$iban\t$bic\t$sepa_id\t$sepa_date\n";

		}

		// write billing files - TODO: chown?
		File::append($booking_records_file, $booking_records);		
		File::append($invoice_records_file, $invoice_records);

		/*
		 * Create SEPA File
		 */
		$msg_id = date('YmdHis');		// km3 uses actual time
		$creditor['name'] = 'ERZNET AG';
		$creditor['iban'] = 'DE64870540000440011094';
		$creditor['bic']  = 'WELADED1STB';
		$creditor['id']   = 'DE95ZZZ00000425253';

		// Set the initial information
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $creditor['name']);

		foreach ($sepa_tx as $type => $records)
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
				$info 		= "Month $month";
				if ($r['started_lastm'])
					$info 	.= ' + '.date('Y-m', strtotime($last_month));
				
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
		File::put($sepa_xml_file, $directDebit->asXML());

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