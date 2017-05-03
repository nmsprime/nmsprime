<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Modules\ProvVoipEnvia\Entities\EnviaOrder;
use \Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController;
use \Modules\ProvBase\Entities\Modem;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaOrderProcessorCommand extends Command {

	/**
	 * The console command name.
	 */
	protected $name = 'provvoipenvia:process_envia_orders';

	/**
	 * The console command description.
	 */
	protected $description = 'Process Envia orders of some special types';

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
	 * @return null
	 */
	public function fire()
	{

		Log::info($this->description);

		echo "\n";
		$this->_process_contract_relocate();

		echo "\n";

	}

	/**
	 * Process orders which relocated contracts
	 *
	 * The problem is that Envia does not change the currently active (Envia) contract – they remove the old and create a new one.
	 * The contractreference changes – but at the orderdate. Changes before this date (e.g. the TRC class) have
	 * to be sent using the OLD reference. So, on orderdate we have to change the contract references…
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_contract_relocate() {

		Log::info('Processing contract/relocate orders');

		// as there can be some delays in status change of orders we have to look back in history a little bit…
		$date_threshold = date('c', strtotime("-2 weeks"));
		$orders = EnviaOrder::where('method', '=', 'contract/relocate')->where('orderdate', '>=', $date_threshold)->get();

		foreach ($orders as $order) {

			$order_phonenumbers = $order->phonenumbers;

			foreach ($order_phonenumbers as $phonenumber) {

				if (!EnviaOrder::order_successful($order)) {
					Log::warning("Order $order->id seems to be pending – will NOT change the contract reference on modem ".$phonenumber->id);
					continue;
				}

				if ($phonenumber->contract_external_id != $order->contractreference) {

					Log::info("Changing contract_external_id for phonenumber ".$phonenumber->id." from ".$phonenumber->contract_external_id." to ".$order->contractreference);
					// we have to set the contract reference to the new value
					// we also could delete $modem->the installation_address_change_date – but I wouldn't do so
					//	⇒ we would lose a bit of our history – and the data is not of any harm
					$phonenumber->contract_external_id = $order->contractreference;
					$phonenumber->save();
				}
			}
		}
	}

}
