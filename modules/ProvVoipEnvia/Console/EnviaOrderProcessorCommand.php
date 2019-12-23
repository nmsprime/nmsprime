<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaContract;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaOrderProcessorCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'provvoipenvia:process_envia_orders';

    /**
     * The console command description.
     */
    protected $description = 'Process envia TEL orders of some special types';

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
    public function handle()
    {
        Log::info($this->description);

        echo "\n";
        $this->_process_contract_relocate();

        echo "\n";
    }

    /**
     * Process orders which relocated contracts
     *
     * The problem is that envia TEL does not change the currently active (envia TEL) contract – they remove the old and create a new one.
     * The contractreference changes – but at the orderdate. Changes before this date (e.g. the TRC class) have
     * to be sent using the OLD reference. So, on orderdate we have to change the contract references…
     *
     * @author Patrick Reichel
     */
    protected function _process_contract_relocate()
    {
        Log::info('Processing contract/relocate orders');

        // as there can be some delays in status change of orders we have to look back in history a little bit…
        $date_threshold = date('c', strtotime('-2 days'));
        $orders = EnviaOrder::whereRaw("method='contract/relocate' OR ordertype='Umzug'")->where('orderdate', '>=', $date_threshold)->get();

        foreach ($orders as $order) {
            $order_phonenumbers = $order->phonenumbers;

            foreach ($order_phonenumbers as $phonenumber) {
                if (! EnviaOrder::order_successful($order)) {
                    Log::warning("Order $order->id seems to be pending – will NOT change the contract reference on modem ".$phonenumber->id);
                    continue;
                }

                if ($phonenumber->contract_external_id != $order->contractreference) {

                    // find old and new enviacontracts; create if not existing
                    $old_enviacontract = EnviaContract::firstOrCreate(['envia_contract_reference' => $phonenumber->contract_external_id]);
                    $new_enviacontract = EnviaContract::firstOrCreate(['envia_contract_reference' => $order->contractreference]);

                    // if there is a next ID on envia contract: This has already been processed
                    if (! is_null($old_enviacontract->next_id)) {
                        continue;
                    }

                    // check if the envia TEL contract to switch to is the currently active one
                    if ($new_enviacontract->state != 'Aktiv') {
                        continue;
                    }

                    $old_enviacontract->envia_contract_reference = $phonenumber->contract_external_id;
                    $old_enviacontract->end_date = date('Y-m-d', strtotime($order->orderdate.' -1 day'));
                    $old_enviacontract->end_reason = 'contract/relocate';
                    $old_enviacontract->next_id = $new_enviacontract->id;
                    $old_enviacontract->save();

                    $copy_fields = [
                        'lock_level',
                        'method',
                        'sla_id',
                        'tariff_id',
                        'variation_id',
                    ];
                    foreach ($copy_fields as $field) {
                        if ($new_enviacontract->{'field'} != $old_enviacontract->{'field'}) {
                            $new_enviacontract->{'field'} = $old_enviacontract->{'field'};
                        }
                    }

                    $new_enviacontract->envia_contract_reference = $order->contractreference;
                    $new_enviacontract->start_date = $order->orderdate;
                    $new_enviacontract->prev_id = $old_enviacontract->id;
                    $new_enviacontract->contract_id = $phonenumber->mta->modem->contract->id;
                    $new_enviacontract->modem_id = $phonenumber->mta->modem->id;
                    $new_enviacontract->save();

                    Log::info('Changing contract_external_id for phonenumber '.$phonenumber->id.' from '.$phonenumber->contract_external_id.' to '.$order->contractreference);
                    // we have to set the contract reference to the new value
                    // we also could delete $modem->the installation_address_change_date – but I wouldn't do so
                    //	⇒ we would lose a bit of our history – and the data is not of any harm
                    $phonenumber->contract_external_id = $order->contractreference;
                    $phonenumber->save();

                    // add relation between phonenumber's management and the new envia contract
                    $mgmt = $phonenumber->PhonenumberManagement;
                    if ($mgmt) {
                        $mgmt->enviacontract_id = $new_enviacontract->id;
                        $mgmt->save();
                    }
                }
            }
        }
    }
}
