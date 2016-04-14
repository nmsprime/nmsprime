<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingBaseTable extends BaseMigration {

	protected $tablename = 'billingbase';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create($this->tablename, function(Blueprint $table)
		{
			$this->up_table_generic($table);
			
			$table->integer('rcd');   // requested collection date (Zahlungsziel)
			$table->enum('currency', ['EUR', 'USD']);
			$table->float('tax');
			$table->string('mandate_ref_template');
			$table->integer('invoice_nr_start');
		});

		DB::update("INSERT INTO ".$this->tablename.' (currency) VALUES("EUR");');

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
