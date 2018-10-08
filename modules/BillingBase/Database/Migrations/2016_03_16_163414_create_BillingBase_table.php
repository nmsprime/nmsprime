<?php

use Illuminate\Database\Schema\Blueprint;

class CreateBillingBaseTable extends BaseMigration
{
    protected $tablename = 'billingbase';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->tinyInteger('rcd'); 			// requested collection date (Zahlungsziel: 1 - 31)
            $table->enum('currency', ['EUR', 'USD']);
            $table->float('tax');
            $table->string('mandate_ref_template');
            $table->integer('invoice_nr_start')->unsigned();
            $table->boolean('split'); 				// split sepa transfer types to different files
            $table->boolean('termination_fix'); 	// termination of items only allowed on last days of month
        });

        // set default values for new installation
        DB::update('INSERT INTO '.$this->tablename.' (currency, tax) VALUES("EUR", 19);');
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
