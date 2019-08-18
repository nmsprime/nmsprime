<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Contract;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaCustomerReferenceGetterCommand extends Command
{
    // get some methods used by several updaters
    use \App\Console\Commands\DatabaseUpdaterTrait;

    /**
     * The console command name.
     */
    protected $name = 'provvoipenvia:get_envia_customer_references';

    /**
     * The console command description.
     */
    protected $description = 'Get missing envia TEL customer references and write to contracts';

    /**
     * Array containing the contracts we want to get the envia TEL customer references for
     */
    protected $contracts_to_get_customer_reference_for = [];

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
    public function handle()
    {
        Log::info($this->description);

        $runs = ['current', 'legacy'];
        foreach ($runs as $run) {
            $this->contracts_to_get_customer_reference_for = [];

            echo "\n";
            $this->_get_contracts($run);

            echo "\n";
            $this->_get_envia_customer_references($run);
        }
    }

    /**
     * Collect all contracts we want to get envia TEL customer reference for
     *
     * @author Patrick Reichel
     */
    protected function _get_contracts($run)
    {
        Log::debug(__METHOD__." started for $run customer numbers");

        // get all contracts without envia reference
        $contracts_without_customer_reference = Contract::whereNull('customer_external_id')->get();

        // keep only those with related phonenumbers having an external contract id
        foreach ($contracts_without_customer_reference as $contract) {
            foreach ($contract->related_phonenumbers() as $phonenumber) {

                // check if there is an external contract id on this phonenumber
                if (is_null($phonenumber->contract_external_id)) {
                    continue;
                }

                // add contract and stop investigation (we don't want to get the customer reference multiple times)
                if ($run == 'current') {
                    $number = $contract->customer_number();
                    if (boolval($number) && ($number != 'n/a')) {
                        array_push($this->contracts_to_get_customer_reference_for, $contract);
                    }
                    break;
                } elseif ($run == 'legacy') {
                    $number = $contract->customer_number_legacy();
                    if (boolval($number) && ($number != 'n/a')) {
                        array_push($this->contracts_to_get_customer_reference_for, $contract);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Get all envia TEL customer references for the contracts
     *
     * @author Patrick Reichel
     */
    protected function _get_envia_customer_references($run)
    {
        Log::debug(__METHOD__." for $run customer numbers started");

        foreach ($this->contracts_to_get_customer_reference_for as $contract) {
            $contract_id = $contract->id;
            Log::info("Getting customer reference for contract $contract_id");

            try {
                // get the relative URL to execute the cron job for updating the current order_id
                if ($run == 'current') {
                    $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'customer_get_reference', 'contract_id' => $contract_id, 'really' => 'True'], false);
                } elseif ($run == 'legacy') {
                    $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'customer_get_reference_by_legacy_number', 'contract_id' => $contract_id, 'really' => 'True'], false);
                }

                $url = $this->base_url.$url_suffix;

                $this->_perform_curl_request($url);
            } catch (Exception $ex) {
                Log::error('Exception getting envia TEL customer reference for contract '.$contract_id.'): '.$ex->getMessage().' => '.$ex->getTraceAsString());
            }
        }
    }
}
