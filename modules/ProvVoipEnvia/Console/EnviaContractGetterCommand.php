<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoipEnvia\Entities\EnviaContract;

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
     * Array holding the envia TEL contracts to be “Gekündigt” in database
     */
    protected $enviacontracts_to_be_canceled = [];

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
    public function handle()
    {
        Log::info($this->description);

        echo "\n";
        $this->getContracts();

        echo "\n";
        $this->cancelEnviacontractsOnLongEndedContracts();

        echo "\n";
        $this->getEnviaContracts();

        echo "\n\n";
    }

    protected function cancelEnviacontractsOnLongEndedContracts()
    {
        foreach ($this->enviacontracts_to_be_canceled as $enviacontract) {
            // manually and older envia contract entries
            Log::warning(__CLASS__.": Setting state for old enviacontract $enviacontract->id to “Nicht ermittelbar”");
            $enviacontract->state = 'Nicht ermittelbar';
            $enviacontract->end_date = '1900-01-01';
            $enviacontract->save();
        }
    }

    /**
     * Collect all of our contracts we want to get envia TEL contracts for
     *
     * @author Patrick Reichel
     */
    protected function getContracts()
    {
        Log::debug(__METHOD__.' started');

        $contracts = Contract::whereNotNull('customer_external_id')->get();
        $msg = 'Found '.count($contracts).' with external_customer_id set.';
        Log::debug(__CLASS__.": $msg");
        $this->line($msg);

        $today = date('Y-m-d');
        $oneYearBack = date('Y-m-d', strtotime('-1 year'));

        $bar = $this->output->createProgressBar(count($contracts));

        $recentlyEnded = [];
        $oldContracts = [];
        foreach ($contracts as $contract) {
            if (
                (! boolval($contract->contract_end))
                ||
                ('0000-00-00' == $contract->contract_end)
                ||
                ($contract->contract_end > $today)
            ) {
                // active contracts
                $hasActiveNumber = false;
                $phonenumbers = $contract->related_phonenumbers();

                // check if there is any active number with existing phonenumbermanagement attached
                foreach ($phonenumbers as $phonenumber) {
                    if (
                        ($phonenumber->active)
                        &&
                        (! is_null($phonenumber->phonenumbermanagement))
                    ) {
                        $hasActiveNumber = true;
                        break;
                    }
                }

                if ($hasActiveNumber) {
                    array_push($this->contracts_to_get_envia_contracts_for, $contract->id);
                }
            } elseif ($contract->contract_end > $oneYearBack) {
                // contract ended within the last 12 months
                // needs special handling because of time shifted end of envia contracts
                $recentlyEnded[] = $contract->id;
            } else {
                // old contracts
                $oldContracts[] = $contract->id;
            }
            $bar->advance();
        }
        $bar->finish();

        // handle enviacontracts for ended nms contracts
        $enviacontracts = EnviaContract::where('state', 'NOT LIKE', 'Gekündigt')->where('state', 'NOT LIKE', 'Nicht ermittelbar')->get();
        Log::debug(__CLASS__.": $msg");

        echo "\n\n";
        $msg = 'Found '.count($enviacontracts).' enviacontracts with state different to “Gekündigt” and “Nicht ermittelbar”.';
        $this->line($msg);
        $bar = $this->output->createProgressBar(count($enviacontracts));

        foreach ($enviacontracts as $enviacontract) {
            if (in_array($enviacontract->contract_id, $recentlyEnded)) {
                $this->contracts_to_get_envia_contracts_for[] = $contract->id;
            } elseif (in_array($enviacontract->contract_id, $oldContracts)) {
                $this->enviacontracts_to_be_canceled[] = $enviacontract;
            }
            $bar->advance();
        }
        $bar->finish();
    }

    /**
     * Get all the envia TEL contracts for our contracts
     *
     * @author Patrick Reichel
     */
    protected function getEnviaContracts()
    {
        Log::debug(__METHOD__.' started');
        Log::info('There are '.count($this->contracts_to_get_envia_contracts_for).' contracts to get enviacontracts for.');

        $this->line('There are '.count($this->contracts_to_get_envia_contracts_for).' contracts to get enviacontracts for.');
        $bar = $this->output->createProgressBar(count($this->contracts_to_get_envia_contracts_for));

        foreach ($this->contracts_to_get_envia_contracts_for as $contract_id) {
            Log::info("Trying to get envia TEL contracts for contract $contract_id");

            try {
                // get the relative URL to execute the cron job for updating the current order_id
                $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'customer_get_contracts', 'contract_id' => $contract_id, 'really' => 'True'], false);

                $url = $this->base_url.$url_suffix;

                $this->_perform_curl_request($url);
            } catch (\Exception $ex) {
                Log::error('Exception getting envia TEL contract for contract '.$contract_id.'): '.$ex->getMessage().' => '.$ex->getTraceAsString());
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
