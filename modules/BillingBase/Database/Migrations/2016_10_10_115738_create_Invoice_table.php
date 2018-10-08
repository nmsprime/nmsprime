<?php

use Illuminate\Database\Schema\Blueprint;

class CreateInvoiceTable extends BaseMigration
{
    protected $tablename = 'invoice';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('contract_id');
            $table->integer('settlementrun_id');
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->string('filename');
            $table->enum('type', ['Invoice', 'CDR']); 	// Invoice or Call Data Record
            $table->string('number'); 			// invoice number
            $table->float('charge', 8, 3);
        });

        $this->set_fim_fields(['number']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice');
    }

    /**
     * Import Invoice Database Entries to adapt Invoice class to Eloquent Model Structure
     *
     * This is only for actual running systems where Database already contains Contracts
     * NOTE: It's save to comment this function out after it was running on deployed systems [12/10/2016]
     *
     * @author Nino Ryschawy
     */
    private static function _invoice_data_import()
    {
        $bool = true;

        $contracts = \Modules\ProvBase\Entities\Contract::all();
        $num = count($contracts);
        $i = 0;

        foreach ($contracts as $contract) {
            if ($bool) {
                echo "Import data for adapted Invoice Model - Ensure that all Settlement Runs are verified!!! Rollback BillingBase module otherwise and migrate after verification again\n";
                $bool = false;
            }

            $i++;
            echo "Contract: create Invoice database entry: $i/$num \r";

            $invoices = \Modules\Ccc\Http\Controllers\CccUserController::get_customer_invoices($contract->id);

            foreach ($invoices as $pdf) {
                $data['contract_id'] = $contract->id;
                $data['filename'] = $pdf->getFilename();
                $fname = explode('_', str_replace('.pdf', '', $data['filename']));

                $data['type'] = 'Invoice';
                if (strpos($data['filename'], '_cdr') !== false) {
                    $data['type'] = 'CDR';
                }

                $data['year'] = $fname[0];
                $data['month'] = $fname[1];

                // get charge and invoice nr from accounting records - creation date for invoices is 1 month ahead, for cdrs it's 2 months
                $offset = $data['type'] == 'Invoice' ? 1 : 2;
                $start = \Carbon\Carbon::create($data['year'], $data['month'], '01', '00', '00', '00')->addMonth($offset);
                $end = \Carbon\Carbon::create($data['year'], $data['month'], '01', '00', '00', '00')->addMonth($offset + 1);

                if ($data['type'] == 'Invoice') {
                    $recs = \Modules\BillingBase\Entities\AccountingRecord::where('contract_id', '=', $contract->id)->whereBetween('created_at', [$start, $end])->get();
                } else {
                    $recs = \Modules\BillingBase\Entities\AccountingRecord::where('contract_id', '=', $contract->id)->whereBetween('created_at', [$start, $end])->where('name', '=', 'Telefone Calls')->get();
                }

                if (! isset($recs[0])) {
                    continue;
                }

                $settlementrun = \Modules\BillingBase\Entities\SettlementRun::whereBetween('created_at', [$start, $end])->get();
                $data['settlementrun_id'] = $settlementrun[0]->id;

                $data['number'] = $fname[0].'/'.$recs[0]->sepa_account_id.'/'.$recs[0]->invoice_nr;
                $data['charge'] = 0;

                foreach ($recs as $record) {
                    $data['charge'] += $record->charge;
                }

                \Modules\BillingBase\Entities\Invoice::create($data);
            }
        }

        echo "\n";
    }
}
