<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaContract;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaOrderUpdaterCommand extends Command
{
    // get some methods used by several updaters
    use \App\Console\Commands\DatabaseUpdaterTrait;

    /**
     * The console command name.
     */
    protected $name = 'provvoipenvia:update_envia_orders';

    /**
     * The console command description.
     */
    protected $description = 'Update envia TEL orders database';

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
     *   - first get csv containing all (phonenumber related) orders from envia and update database using ProvVoipEnvia model ⇒ this will get possible orders that has been manually created
     *   - second get status for each single order ⇒ this will update contract/customer related orders as well
     *
     * @return null
     */
    public function handle()
    {
        Log::info($this->description);

        echo "\n";
        $this->_get_all_orders_csv();

        echo "\n";
        $this->_get_orders();

        echo "\n";
        $this->_update_orders();
        echo "\n";

        echo "\n";
        $this->_update_order_relation_to_contracts();
        echo "\n";
    }

    /**
     * Gets CSV for all phonenumber related orders from envia and updates database via model ProvVoipEnvia
     *
     * This happenes order by order (each with a single request) to avoid timeouts which can leave the database in undefined state!
     *
     * @author Patrick Reichel
     */
    protected function _get_all_orders_csv()
    {
        Log::debug(__METHOD__.' started');
        Log::info('Getting all orders csv');

        // create URL suffix
        $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'misc_get_orders_csv', 'really' => 'True'], false);

        // build complete URL
        $url = $this->base_url.$url_suffix;

        // execute using cURL
        $result = $this->_perform_curl_request($url);

        // the result should be an array containing the orders – if not there has been a problem…
        try {
            $orders = unserialize($result);
        } catch (Exception $ex) {
            Log::error('Exception deserializing expected envia TEL orders array created from CSV ('.$ex->getMessage().') – cannot proceed');

            return;
        }

        if (! is_array($orders)) {
            Log::error('Received no unserializable data – cannot proceed');

            return;
        }

        // call the special method in ProvVoipEnvia to update the orders one by one
        foreach ($orders as $order) {
            $param = urlencode(serialize($order));
            $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'misc_get_orders_csv_process_single_order', 'serialized_order' => $param, 'really' => 'True'], false);
            $url = $this->base_url.$url_suffix;

            $result = $this->_perform_curl_request($url);
            echo "\n";
            print_r($result);
        }
    }

    /**
     * Get all the envia TEL orders to be updated.
     * Currently this is an simple select – later we could add some more checks: E.g. don't get updates for orders in final state.
     *
     * @author Patrick Reichel
     */
    protected function _get_orders()
    {
        Log::debug(__METHOD__.' started');
        Log::info('Getting orders from database');
        $this->orders = EnviaOrder::all();
    }

    /**
     * Update the relevant orders.
     *
     * @author Patrick Reichel
     */
    protected function _update_orders()
    {
        Log::debug(__METHOD__.' started');

        foreach ($this->orders as $order) {
            $order_id = $order->orderid;

            // if current order is not in final state: update
            if (! EnviaOrder::orderstate_is_final($order)) {
                Log::debug('Updating order '.$order_id);

                try {
                    // get the relative URL to execute the cron job for updating the current order_id
                    $url_suffix = \URL::route('ProvVoipEnvia.cron', ['job' => 'order_get_status', 'order_id' => $order_id, 'really' => 'True'], false);

                    $url = $this->base_url.$url_suffix;

                    $this->_perform_curl_request($url);

                    if ($this->_updated($order_id)) {
                        Log::info('Updated Order id '.$order_id.'.');
                    }
                } catch (Exception $ex) {
                    Log::error('Exception updating order '.$order_id.'): '.$ex->getMessage().' => '.$ex->getTraceAsString());
                }
            }
        }
    }

    /**
     * Check if the given order id has been updated.
     * This simply compares database “updated_at” against the system clock.
     *
     * @author Patrick Reichel
     *
     * @param $order_id ID of the order to be checked
     */
    protected function _updated($order_id)
    {
        Log::debug(__METHOD__.' started');

        // not older than 1 hour (this is relatively long; but there are some timing issues and
        // the script is run late at night…)
        $compare_time = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $order = EnviaOrder::withTrashed()->where('orderid', '=', $order_id)->first();

        // if order is deleted it has been updated
        if (boolval($order->deleted_at)) {
            return true;
        }

        // else: compare times
        if ($order->updated_at >= $compare_time) {
            return true;
        }

        return false;
    }

    /**
     * Try to create a relation to EnviaContract if not set
     *
     * @author Patrick Reichel
     */
    protected function _update_order_relation_to_contracts()
    {
        Log::debug(__METHOD__.' started');

        $envia_orders = EnviaOrder::whereRaw('enviacontract_id IS NULL')->get();

        $envia_contract = new EnviaContract();
        foreach ($envia_orders as $envia_order) {

            // if there is no contract reference we are not able to find a match
            if (! $envia_order->contractreference) {
                continue;
            }

            // try to get the corresponding envia contract
            $envia_contract = EnviaContract::firstOrNew(['envia_contract_reference' => $envia_order->contractreference]);

            if (! $envia_contract->exists) {
                $envia_contract->envia_customer_reference = $envia_order->customerreference;
                $envia_contract->save();
            }

            $envia_order->enviacontract_id = $envia_contract->id;
            $envia_order->save();
        }
    }
}
