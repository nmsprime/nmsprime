<?php

use Illuminate\Database\Schema\Blueprint;

class CreateSepaAccountTable extends BaseMigration
{
    protected $tablename = 'sepaaccount';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Storage::makeDirectory('config/billingbase/template/', '744', true);
        Storage::makeDirectory('config/billingbase/logo/', '744', true);
        system('/bin/chown -R apache '.storage_path('app/config/billingbase'));

        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('name');
            $table->string('holder');
            $table->string('creditorid', 35);
            $table->string('iban', 34);
            $table->string('bic', 11);
            $table->string('institute');
            $table->integer('company_id');
            $table->string('invoice_headline');
            $table->string('invoice_text');
            $table->string('invoice_text_negativ');
            $table->string('invoice_text_sepa');
            $table->string('invoice_text_sepa_negativ');
            $table->string('template_invoice');
            $table->string('template_cdr');
            $table->string('description');
        });

        $this->set_fim_fields(['name', 'holder', 'iban', 'bic', 'institute', 'invoice_headline', 'description', 'invoice_text', 'invoice_text_negativ', 'invoice_text_sepa_negativ', 'invoice_text_sepa']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tablename);
    }
}
