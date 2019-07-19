<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\TRCClass;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\PhonenumberManagement;

/**
 * Class for updating database with voice data; this is used to fill gaps in phonenumber (e.g. sip username or password) and phonenumbermanagement (e.g. TRC class)
 */
class VoiceDataUpdaterCommand extends Command
{
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
    protected $description = 'Update phonenumber/phonenumbermanagement data {default|complete}';

    /**
     * The signature (defining the optional argument)
     */
    protected $signature = 'provvoipenvia:update_voice_data
							{mode=default : The mode to run in; give argument “complete” to get envia TEL voice data for all phonenumbers (and not only for those with missing data}';

    // store for contract ids for which we want to get voice data
    protected $affected_contracts = [];

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
    public function handle()
    {
        Log::debug(__METHOD__.' started');
        Log::info($this->description);

        if (! in_array($this->argument('mode'), ['default', 'complete'])) {
            echo 'Usage: '.$this->argument('command')." {default|complete}\n";
            exit(1);
        }

        Log::info('Chosen mode is '.$this->argument('mode'));

        echo "\n";
        $this->_get_envia_sip_contracts($this->argument('mode'));

        echo "\n";
        $this->_get_envia_mcgp_contracts($this->argument('mode'));

        echo "\n";
        $this->_update_voice_data();

        echo "\n";
    }

    /**
     * Get all envia TEL SIP contracts we want to get voice data for.
     *
     * @author Patrick Reichel
     */
    protected function _get_envia_sip_contracts($mode)
    {
        Log::debug(__METHOD__.' started');

        if ($mode == 'default') {
            $where_stmt = "
				active != 0 AND (
				sipdomain IS NULL OR sipdomain LIKE '' OR
				username IS NULL OR username LIKE '' OR
				password IS NULL OR password LIKE ''
				)
			";
            $phonenumbers = Phonenumber::whereRaw($where_stmt)->get();
        } elseif ($mode == 'complete') {
            $phonenumbers = Phonenumber::where('active', '!=', '0')->get();
        } else {
            return;
        }

        // get all phonenumbermanagements having TRCClass not set
        $trc_null = TRCClass::whereRaw('trc_id IS NULL')->first();
        $trc_null_id = $trc_null->id;
        $phonenumbermanagements = PhonenumberManagement::where('trcclass', '=', $trc_null_id);
        foreach ($phonenumbermanagements as $phonenumbermanagent) {
            $phonenumbers->push($phonenumbermanagement->phonenumber);
        }

        // process numbers and check if update has to be done
        foreach ($phonenumbers as $phonenumber) {

            // check if phonenumber is SIP (this can be determined from mta type)
            $mta = $phonenumber->mta;
            if (is_null($mta) || ($mta->type != 'sip')) {
                continue;
            }

            // check if we have an envia TEL contract ID for this phonenumber
            if (! $phonenumber->contract_external_id) {
                continue;
            }

            // check if a phonenumbermanagement exists (there is data to be changed, to)
            if (is_null($phonenumber->phonenumbermanagement)) {
                continue;
            }

            // add phonenumber to our contracts array
            // we safely can overwrite existing numbers ⇒ method call is based on phonenumber and extracts envia TEL contract ID from this
            $this->affected_contracts[$phonenumber->contract_external_id] = $phonenumber->id;
        }
    }

    /**
     * Get all order IDs for packet cable numbers with missing data.
     *
     * @author Patrick Reichel
     *
     * @todo: Currently there are only SIP numbers – so this is a placeholder. Implement if there are packet cable accounts.
     */
    protected function _get_envia_mcgp_contracts()
    {

        // do nothing
    }

    /**
     * Update database
     *
     * @author Patrick Reichel
     */
    protected function _update_voice_data()
    {
        Log::debug(__METHOD__.' started');

        foreach ($this->affected_contracts as $envia_contract_ref => $phonenumber_id) {
            Log::info('Getting voice data for envia contract '.$envia_contract_ref);

            // get the relative URL to execute the cron job for updating the current contract_id
            $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'contract_get_voice_data', 'phonenumber_id' => $phonenumber_id, 'really' => 'True'], false);

            $url = $this->base_url.$url_suffix;

            $this->_perform_curl_request($url);
        }
    }
}
