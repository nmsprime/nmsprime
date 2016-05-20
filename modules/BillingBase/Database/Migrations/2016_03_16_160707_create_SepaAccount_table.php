<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSepaAccountTable extends BaseMigration {

	protected $tablename = 'sepaaccount';

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
			$table->integer('company_id');
			$table->string('invoice_headline');
			$table->string('invoice_text');
			$table->string('invoice_text_negativ');
			$table->string('invoice_text_sepa');
			$table->string('invoice_text_sepa_negativ');
			$table->string('template');
			$table->string('description');
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
