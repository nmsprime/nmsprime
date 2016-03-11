<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Contract;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Modules\BillingBase\Entities\Price;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use File;
use DB;

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
		 * TODO
		 * add to app/Console/Kernel.php -> run monthly()->when(function(){ date('Y-m-d') == date('Y-m-10')}) for tenth day in month
		 * add every new accounting record to the table for every contract once in a month
		 * table columns: name, contract_id, price    --- needed for creating the direct debit xml
		 * if there is already one entry in this month we should stop executing and not adding same entries again - only executable once in a month
		 * every item that was added to a contract after the last run of this function has to be considered during the next run
		 */

		$logger = new Logger('Billing');
		$logger->pushHandler(new StreamHandler(storage_path().'/logs/billing-'.date('Y-m').'.log'), Logger::DEBUG, false);
		$logger->addInfo(' #####	Creating Accounting Record table 	#####');


		// TODO: replace all date('Y-m') with $month!!
		$now = date('Y-m-d');
		$month = date('Y-m');
		$last_month = date('Y-m-01', strtotime('-1 months', strtotime($now)));
		$this_month = date('Y-m-01');
		$next_month = date('Y-m-01', strtotime('+1 months', strtotime($now)));
		$m_in_sec = 60*60*24*30;	// month in seconds


		// remove all entries of this month (if entries were already created) and create them new
		$actually_created = DB::table($this->tablename)->where('created_at', '>=', $this_month)->where('created_at', '<=', $next_month)->first();
		if (is_object($actually_created))
		{
			$logger->addNotice('Table was already created this month - Recreate it!');
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
			$invoice_nr = 1000000;
		}
		$logger->addDebug('Last creation of table was on '.$last_run);
		

		
		/*
		 * Loop over all Contracts
		 	* Log all Contracts that are out of date
		 	* proof if contract expires/d
		 		* data & voip costs proportional to month (adapt to duration)
		 */
		$cs = Contract::all();
		foreach ($cs as $c)
		{
			// total costs this month for this contract
			$charge = 0;

			// contract is out of date
			if ($c->contract_start >= $now || ($c->contract_end != '0000-00-00' && $c->contract_end < $now))
			{
				$logger->addNotice('Contract '.$c->id.' is out of date');
				continue;
			}

			$ratio = null;
			$expires = false;
			// contract starts this month
			if (date('Y-m', strtotime($now)) == date('Y-m', strtotime($c->contract_start)))
			{
				$days_remaining = date('t') - date('d', strtotime($c->contract_start));
				$logger->addDebug('Contract '.$c->id." starts this month - billing for $days_remaining days");
				$ratio = $days_remaining/date('t');
			}			
			// contract expires this month - contract ends always on end of month - shall be dynamic???
			if (date('Y-m', strtotime($now)) == date('Y-m', strtotime($c->contract_end)))
			{
				$days_remaining = date('d', strtotime($c->contract_end)) - date('d', strtotime($this_month));
				// $ratio = $days_remaining/date('t');
				$logger->addDebug('Contract '.$c->id." expires this month - billing for $days_remaining days");
				$expires = true;
			}

			// add internet and voip tariffs
			$tariff = null;
			$tariff['inet'] = Price::find($c->price_id);
			if ($c->voip_tariff != '')
				$tariff['voip'] = Price::where('voip_tariff', '=', $c->voip_tariff)->get()->all()[0];

			// if ($c->id == 500261) dd($c, $tariff, $c->voip_tariff);

			foreach ($tariff as $t)
			{
				if ($t)
				{
					$price = $t->price;
					if (isset($ratio))
						$price = round($t->price * $ratio, 2);

					// var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$t->name.'", '.$price.", NOW(), '.$invoice_nr.')');
					DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$t->name.'", '.$price.', NOW(), '.$invoice_nr.')');

					$charge += $price;
				}
			}

			/*
			 * add monthly item costs for following items:
			 	* monthly (and no payment_to date ??)
			 	* once and created within last billing period
			 	* once and actual run is within payment_from and payment_to date
			 	* what's with yearly payed items ??
			 	* calculate total sum of items if contract expires
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
							// dd($total_months, $item);
							}
						}

						break;
					case 'Yearly':
						if (date('Y-m') == date('Y-m', strtotime("+1 year", strtotime($item->created_at))))
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

				// var_dump("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at) VALUES(".$c->id.', "'.$price_entry->name.$text.'", '.$entry_cost.', NOW(), '.$invoice_nr.')');
				DB::update("INSERT INTO ".$this->tablename." (contract_id, name, price, created_at, invoice_nr) VALUES(".$c->id.', "'.$price_entry->name.$text.'", '.$entry_cost.', NOW(), '.$invoice_nr.')');

				$charge += $entry_cost;
			}


			// BUCHUNGSSATZ
			// RECHNUNGSSATZ  - after loop!?



			/*
			 * Add Sepa data to array to order first, recurring and last transactions
			 * Check if valid mandate exists
 		 	 * Log all Contracts that are out of date or don't have a valid sepa mandate
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
				continue;
			}

			$t = 'recurring';
			if (date('Y-m', strtotime($c->contract_start)) == date('Y-m') && !$mandate->recurring)
				$t = 'first';
			else if (date('Y-m', strtotime($c->contract_end)) == date('Y-m'))
				$t = 'last';

			$sepa_tx[$t][] = ['mandate' => $mandate, 'charge' => $charge, 'invoice_nr' => $invoice_nr];

			$invoice_nr += 1;
		}


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
			switch($type)
			{
				case 'first'	: $t = PaymentInformation::S_FIRST; 	break;
				case 'last' 	: $t = PaymentInformation::S_FINAL; 	break;
				case 'recurring': $t = PaymentInformation::S_RECURRING; break;
			}

			// create a payment
			$directDebit->addPaymentInfo($msg_id, array(
			    'id'                    => $msg_id,
			    'creditorName'          => $creditor['name'],
			    'creditorAccountIBAN'   => $creditor['iban'],
			    'creditorAgentBIC'      => $creditor['bic'],
			    'seqType'               => $t,
			    'creditorId'            => $creditor['id']
			));

			foreach($records as $r)
			{
				$payment_id = 'RG '.$r['invoice_nr'];
				
				// Add a Single Transaction to the named payment
				$directDebit->addTransfer($payment_id, array(
				    'amount'                => $r['charge'],
				    'debtorIban'            => $r['mandate']->sepa_iban,
				    'debtorBic'             => $r['mandate']->sepa_bic,
				    'debtorName'            => $r['mandate']->sepa_holder,
				    'debtorMandate'         => $r['mandate']->reference,
				    'debtorMandateSignDate' => $r['mandate']->signature_date,
				    'remittanceInformation' => "Month $month",
				));
			}
		}


		// Retrieve the resulting XML
		File::put('/var/www/lara/test.xml',$directDebit->asXML());

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