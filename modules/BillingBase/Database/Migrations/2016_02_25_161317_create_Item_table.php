<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "item";

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

			$table->integer('contract_id')->unsigned();
			$table->integer('price_id')->unsigned();
			$table->date('payment_from');
			$table->date('payment_to');
			$table->float('credit_amount');
			$table->text('accounting_text');
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
