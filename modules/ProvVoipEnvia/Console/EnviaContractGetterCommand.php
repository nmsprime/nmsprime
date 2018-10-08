<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Contract;

/**
 * Class to get envia TEL contracts by customer
 */
class EnviaContractGetterCommand extends Command
{
    // get some methods used by several updaters
    use \App\Console\Commands\DatabaseUpdaterTrait;

    /**
     * The console command name.
     */
    protected $name = 'provvoipenvia:get_envia_contracts_by_customer';

    /**
     * The console command description.
     */
    protected $description = 'Get envia TEL contracts by customer';

    /**
     * The signature (defining the optional argument)
     */
    protected $signature = 'provvoipenvia:get_envia_contracts_by_customer';

    /**
     * Array holding the contracts (ours, not envia TEL contracts) to get envia TEL contracts for
     */
    protected $contracts_to_get_envia_contracts_for = [];

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
     * Execute the console command
     */
    public function fire()
    {
        Log::info($this->description);

        echo "\n";
        $this->_get_contracts();

        echo "\n";
        $this->_get_envia_contracts();
    }

    /**
     * Collect all of our contracts we want to get envia TEL contracts for
     *
     * @author Patrick Reichel
     */
    protected function _get_contracts()
    {
        Log::debug(__METHOD__.' started');

        $contracts = Contract::all();

        foreach ($contracts as $contract) {

            // get only for active contracts
            if (
                (boolval($contract->contract_end))
                &&
                ($contract->contract_end != '0000-00-00')
                &&
                ($contract->contract_end <= date('Y-m-d'))
            ) {
                continue;
            }

            $has_active_number = false;
            $phonenumbers = $contract->related_phonenumbers();

            // check if there is any active number with existing phonenumbermanagement attached
            foreach ($phonenumbers as $phonenumber) {
                if (
                    ($phonenumber->active)
                    &&
                    (! is_null($phonenumber->phonenumbermanagement))
                ) {
                    $has_active_number = true;
                    break;
                }
            }

            if ($has_active_number) {
                array_push($this->contracts_to_get_envia_contracts_for, $contract->id);
            }
        }
    }

    /**
     * Get all the envia TEL contracts for our contracts
     *
     * @author Patrick Reichel
     */
    protected function _get_envia_contracts()
    {
        Log::debug(__METHOD__.' started');

        foreach ($this->contracts_to_get_envia_contracts_for as $contract_id) {
            Log::info("Trying to get envia TEL contracts for contract $contract_id");

            try {
                // get the relative URL to execute the cron job for updating the current order_id
                $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'customer_get_contracts', 'contract_id' => $contract_id, 'really' => 'True'], false);

                $url = $this->base_url.$url_suffix;

                $this->_perform_curl_request($url);
            } catch (Exception $ex) {
                Log::error('Exception getting envia TEL contract for contract '.$contract_id.'): '.$ex->getMessage().' => '.$ex->getTraceAsString());
            }
        }
    }
}
