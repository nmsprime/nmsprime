<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Modules\ProvVoip\Entities\PhonenumberManagement;
use \Modules\ProvVoip\Entities\Phonenumber;
use \Modules\ProvVoip\Entities\Mta;
use \Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController;

/**
 * Class for updating database with voice data; this is used to fill gaps in phonenumber (e.g. sip username or password) and phonenumbermanagement (e.g. TRC class)
 */
class VoiceDataUpdaterCommand extends Command {

	// get some methods used by several updaters
	use \App\Console\Commands\DatabaseUpdaterTrait;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'provvoipenvia:update_voice_data';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update phonenumber/phonenumbermanagement data';

	// store for contract ids for which we want to get voice data
	protected $affected_contracts = array();

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
	 * Basically this does two jobs:
	 *   - first get contract IDs for all phonenumbers with missing data
	 *   - second try to get voice data for this phonenumbers to update database
	 *
	 * @return null
	 */
	public function fire()
	{
		Log::debug(__METHOD__." started");
		Log::info($this->description);

		echo "\n";
		$this->_get_affected_sip_orders();

		echo "\n";
		$this->_get_affected_mgcp_orders();

		echo "\n";
		$this->_update_voice_data();

		echo "\n";

	}


	/**
	 * Get all order IDs for SIP numbers with missing data.
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_affected_sip_orders() {

		Log::debug(__METHOD__." started");

		$where_stmt = "
			username IS NULL OR username LIKE '' OR
			password IS NULL OR password LIKE '' OR
			sipdomain IS NULL OR sipdomain LIKE ''
		";

		// get all phonenumbers containing empty fields
		$phonenumbers = Phonenumber::whereRaw($where_stmt)->get();

		foreach ($phonenumbers as $phonenumber) {

			// check if phonenumber is SIP (this can be determined from mta type)
			$mta = $phonenumber->mta;
			if (!is_null($mta) && ($mta->type == 'sip')) {

				// get modem
				$modem = $mta->modem;
				if (!is_null($modem)) {

					// get contract
					$contract = $modem->contract;
					if (!is_null($contract)) {

						// add to orders array
						$contract_id = $contract->id;
						if (boolval($contract->contract_external_id) && (!in_array($contract_id, $this->affected_contracts))) {
							array_push($this->affected_contracts, $contract_id);
						}
					}
				}
			};
		};

	}


	/**
	 * Get all order IDs for packet cable numbers with missing data.
	 *
	 * @author Patrick Reichel
	 *
	 * @todo: Currently there are only SIP numbers – so this is a placeholder. Implement if there are packet cable accounts.
	 */
	protected function _get_affected_mgcp_orders() {

		// do nothing
	}


	/**
	 * Update database
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_voice_data() {

		Log::debug(__METHOD__." started");

		foreach ($this->affected_contracts as $contract_id) {

			Log::debug('Updating contract '.$contract_id);

			// get the relative URL to execute the cron job for updating the current contract_id
			$url_suffix = \URL::route("ProvVoipEnvia.cron", array('job' => 'contract_get_voice_data', 'contract_id' => $contract_id, 'really' => 'True'), false);

			$url = $this->base_url.$url_suffix;

			$this->_perform_curl_request($url);

		}

	}

}
