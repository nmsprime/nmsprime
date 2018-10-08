<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateCostCenterMvInvoiceNrStart extends BaseMigration
{
    protected $tablename = 'billingbase';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $nr = \Modules\BillingBase\Entities\BillingBase::get(['invoice_nr_start'])->first()->invoice_nr_start;

        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('invoice_nr_start');
        });

        Schema::table('sepaaccount', function (Blueprint $table) {
            $table->integer('invoice_nr_start')->unsigned()->nullable();
        });

        DB::table('sepaaccount')->update(['invoice_nr_start' => $nr]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $nr = \Modules\BillingBase\Entities\SepaAccount::get(['invoice_nr_start'])->first()->invoice_nr_start;

        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('invoice_nr_start')->unsigned()->nullable();
        });

        Schema::table('sepaaccount', function (Blueprint $table) {
            $table->dropColumn('invoice_nr_start');
        });

        DB::table($this->tablename)->update(['invoice_nr_start' => $nr]);
    }
}
