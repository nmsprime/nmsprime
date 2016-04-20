<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSepaAccountTable extends BaseMigration {

	protected $tablename = 'sepa_account';

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
			
			$table->string('name');
			$table->string('holder');
			$table->string('creditorid', 35);
			$table->string('iban', 34);
			$table->string('bic', 11);
			$table->string('institute');
			$table->string('description');
			$table->integer('company_id');
		});

		$this->set_fim_fields(['name', 'holder', 'iban', 'bic', 'institute', 'description']);

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
