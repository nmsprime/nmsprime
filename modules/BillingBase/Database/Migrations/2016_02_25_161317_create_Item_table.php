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
			$table->integer('product_id')->unsigned();
			$table->tinyInteger('count')->unsigned()->default(1);
			$table->date('valid_from');
			$table->date('valid_to');
			$table->float('credit_amount')->nullable();
			$table->text('accounting_text');
		});

		$this->set_fim_fields(['accounting_text']);

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
