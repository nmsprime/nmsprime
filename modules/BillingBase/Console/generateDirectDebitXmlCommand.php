<?php 
namespace Modules\Billingbase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\ProvBase\Entities\Contract;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use File;

class generateDirectDebitXmlCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nms:ddxml';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Billing Direct Debit XML for Bank for all Contracts';

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
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$contract = Contract::first();

		$msg_id = $payment_id = '001'; // km3 uses actual time
		$creditor_name = 'ERZNET AG';
		$creditor_iban = 'DE64870540000440011094';
		$creditor_bic = 'WELADED1STB';
		$creditor_id = 'DE95ZZZ00000425253';

		// Set the initial information
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $creditor_name);

		// create a payment, it's possible to create multiple payments,
		// "firstPayment" is the identifier for the transactions
		$directDebit->addPaymentInfo($payment_id, array(
		    'id'                    => $payment_id,
		    'creditorName'          => $creditor_name,
		    'creditorAccountIBAN'   => $creditor_iban,
		    'creditorAgentBIC'      => $creditor_bic,
		    'seqType'               => PaymentInformation::S_FIRST,
		    'creditorId'            => $creditor_id
		));
		// Add a Single Transaction to the named payment
		$directDebit->addTransfer($payment_id, array(
		    'amount'                => '500',
		    'debtorIban'            => 'FI1350001540000056',
		    'debtorBic'             => 'OKOYFIHH',
		    'debtorName'            => 'Their Company',
		    'debtorMandate'         =>  'AB12345',
		    'debtorMandateSignDate' => '13.10.2012',
		    'remittanceInformation' => 'Purpose of this direct debit'
		));
		// Retrieve the resulting XML
		File::put('/var/www/lara/test.xml',$directDebit->asXML());
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			// ['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			// ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
