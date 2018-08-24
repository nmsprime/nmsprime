<?php

use Illuminate\Database\Schema\Blueprint;

class CreateSepaMandateTable extends \BaseMigration
{
    // name of the table to create
    protected $tablename = 'sepamandate';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sepamandate', function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('contract_id');
            $table->string('reference');
            $table->date('signature_date');
            $table->string('sepa_holder');
            $table->string('sepa_iban', 34);
            $table->string('sepa_bic', 11);
            $table->string('sepa_institute');
            $table->date('sepa_valid_from')->nullable();
            $table->date('sepa_valid_to')->nullable();
            $table->boolean('recurring');
            $table->enum('state', ['', 'FIRST', 'RECUR', 'LAST']);		// type that was sent in last SepaXml
        });

        $this->set_fim_fields(['reference', 'sepa_holder', 'sepa_institute', 'sepa_iban', 'sepa_bic']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sepamandate');
    }
}
