<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "accounting";

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

			$table->integer('contract_id');
			$table->string('name');
			$table->integer('product_id');
			$table->float('ratio', 6, 4);
			$table->tinyInteger('count');
			$table->integer('sepa_account_id');			// for creating individual invoice numbers
			$table->integer('invoice_nr');
		});

		return parent::up();
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
