<?php

namespace Modules\BillingBase\Entities;

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;

use File;

class Sepaxml {

	// 1 Sepaxml for every account -> 2 files for every Sepaxml (debit, credit)

	private $debits  = [];
	private $credits = [];

	private $creditor;
	private $msg_id;
	private $dd_file;
	private $dc_file;

	private $debit_data = array(

		'amount'                => '',
		'debtorIban'            => '',
		'debtorBic'             => '',
		'debtorName'            => '',
		'debtorMandate'         => '',
		'debtorMandateSignDate' => '',
		'remittanceInformation' => '',
		);

	private $credit_data = array(

		'amount'                  => '',
		'creditorIban'            => '',
		'creditorBic'             => '',
		'creditorName'            => '',
		'remittanceInformation'   => ''
		);


	public function __construct($sepa_account)
	{
		$this->creditor = array(
			'name' => $sepa_account->name,
			'iban' => $acc->iban;
			'bic'  => $acc->bic;
			'id'   => $acc->creditorid;			);
		
		$this->msg_id = date('YmdHis').$sepa_account->id;		// km3 uses actual time
		$dd_file 	= storage_path('billing/sepa_dd_').$sepa_account->name.'.xml';
		$dc_file 	= storage_path('billing/sepa_dc_').$sepa_account->name.'.xml';
	}

	
	// Note: Charge == 0 is automatically excluded
	public function add_entry($mandate, $value, $dates, $invoice_nr)
	{
		$info = 'Month '.date('m/Y');

		if ($value < 0)
		{
			$this->credit_data['amount']                = $value * (-1);
			$this->credit_data['creditorIban']          = $mandate->sepa_iban;
			$this->credit_data['creditorBic']           = $mandate->sepa_bic;
			$this->credit_data['creditorName']          = $mandate->sepa_holder;
			$this->credit_data['remittanceInformation'] = $info;

			$this->credits[] = $this->credit_data;
		}
		else
		{
			// determine transaction type: first/recurring/final
			$type = PaymentInformation::S_RECURRING;
			// started this month or last month after last run of accounting command
			if (!$mandate->recurring && date('Y-m', strtotime($mandate->c->contract_start)) == $dates['m'] || (date('Y-m', strtotime($mandate->c->contract_start)) == $dates['last_m_Y'] && strtotime($mandate->c->contract_start) > strtotime($dates['last_run'])))
				$type = PaymentInformation::S_FIRST;
			// else if (date('Y-m', strtotime($mandate->c->contract_end)) == $this->dates['m'])
			else if ($mandate->c->expires)
				$type = PaymentInformation::S_FINAL;

			$this->debit_data['endToEndId']			   => 'RG '.$invoice_nr;
			$this->debit_data['amount']                => $value;
			$this->debit_data['debtorIban']            => $mandate->sepa_iban;
			$this->debit_data['debtorBic']             => $mandate->sepa_bic;
			$this->debit_data['debtorName']            => $mandate->sepa_holder;
			$this->debit_data['debtorMandate']         => $mandate->reference;
			$this->debit_data['debtorMandateSignDate'] => $mandate->signature_date;
			$this->debit_data['remittanceInformation'] => $info;
			
			$this->debits[$type][] = $this->debit_data;
		}
	}


	/**
	 * Create SEPA XML
	 */
	protected function make_sepa_xml()
	{
		$this->make_credit_file();
		$this->make_debit_file();
	}


	private function make_debit_file()
	{
		if (!$this->debits)
			return;

		// Set the initial information for direct debits
		$directDebit = TransferFileFacadeFactory::createDirectDebit($this->msg_id, $this->creditor['name']);

		foreach ($this->debits as $type => $records)
		{
			// create a payment
			$directDebit->addPaymentInfo($this->msg_id, array(
				'id'                    => $this->msg_id,
				'creditorName'          => $this->creditor['name'],
				'creditorAccountIBAN'   => $this->creditor['iban'],
				'creditorAgentBIC'      => $this->creditor['bic'],
				'seqType'               => $type,
				'creditorId'            => $this->creditor['id'],
				// 'dueDate'				=> // requested collection date (Fälligkeits-/Ausführungsdatum) - from global config
			));

			// Add Transactions to the named payment
			foreach($records as $r)
				$directDebit->addTransfer($this->msg_id, $r);

		}

		// Retrieve the resulting XML
		File::put($this->dd_file, $directDebit->asXML());
		echo "stored sepa direct debit xml in".$this->dd_file."\n";
	}

	private function make_credit_file()
	{
		if (!$this->credits)
			return;
		
		// Set the initial information for direct credits
		$customerCredit = TransferFileFacadeFactory::createCustomerCredit($this->msg_id.'C', $this->creditor['name']);

		$customerCredit->addPaymentInfo($this->msg_id.'C', array(
			'id'                      => $this->msg_id.'C',
			'debtorName'              => $this->creditor['name'],
			'debtorAccountIBAN'       => $this->creditor['iban'],
			'debtorAgentBIC'          => $this->creditor['bic'],
		));

		// Add Transactions to the named payment
		foreach($this->credits as $r)
			$customerCredit->addTransfer($this->msg_id.'C', $r);


		// Retrieve the resulting XML
		File::put($this->dc_file, $customerCredit->asXML());
		echo "stored sepa direct credit xml in ".$this->dc_file."\n";

	}



}