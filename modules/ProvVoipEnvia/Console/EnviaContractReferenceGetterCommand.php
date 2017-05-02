<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Modules\ProvVoip\Entities\Phonenumber;
use \Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaContractReferenceGetterCommand extends Command {

	// get some methods used by several updaters
	use \App\Console\Commands\DatabaseUpdaterTrait;

	/**
	 * The console command name.
	 */
	protected $name = 'provvoipenvia:get_envia_contract_references';

	/**
	 * The console command description.
	 */
	protected $description = 'Get missing Envia contract references and write to phonenumbers';

	/**
	 * Array containing the phonenumbers we want to get the Envia contract references for
	 */
	protected $phonenumbers_to_get_contract_reference_for = array();

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// this comes from config/app.php (key 'url')
		$this->base_url = \Config::get('app.url');

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return null
	 */
	public function fire() {

		Log::info($this->description);

		echo "\n";
		$this->_get_phonenumbers();

		echo "\n";
		$this->_get_envia_contract_references();
	}

	/**
	 * Collect all phonenumbers we want to get Envia contract reference for
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_phonenumbers() {

		Log::debug(__METHOD__." started");

		// get all numbers without envia reference
		$phonenumbers_without_contract_reference = Phonenumber::whereNull('contract_external_id')->get();

		// keep only those with existing
		foreach ($phonenumbers_without_contract_reference as $phonenumber) {

			// check if number under investigation has a phonenumbermanagement
			if (!$phonenumber->phonenumbermanagement) {
				continue;
			}

			// check if activation date is set
			if (!$phonenumber->phonenumbermanagement->activation_date) {
				continue;
			}

			// check if deactivation date is more than one week in the past
			$max_deactivation_date = date('Y-m-d', strtotime("-1 week"));
			if (
				($phonenumber->phonenumbermanagement->deactivation_date)
				&&
				($phonenumber->phonenumbermanagement->deactivation_date < $max_deactivation_date)
			) {
				continue;
			}

			array_push($this->phonenumbers_to_get_contract_reference_for, $phonenumber);
		}
	}

	/**
	 * Get all Envia contract references for the phonenumbers
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_envia_contract_references() {

		Log::debug(__METHOD__." started");

		foreach ($this->phonenumbers_to_get_contract_reference_for as $phonenumber) {

			$phonenumber_id = $phonenumber->id;
			Log::debug("Updating phonenumber $phonenumber_id");

			try {
				// get the relative URL to execute the cron job for updating the current order_id
				$url_suffix = \URL::route("ProvVoipEnvia.cron", array('job' => 'contract_get_reference', 'phonenumber_id' => $phonenumber_id, 'really' => 'True'), false);

				$url = $this->base_url.$url_suffix;

				$this->_perform_curl_request($url);

			}
			catch (Exception $ex) {
				Log::error("Exception getting Envia contract reference for phonenumber ".$phonenumber_id."): ".$ex->getMessage()." => ".$ex->getTraceAsString());
			}
		}
	}

}
