<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSepaMandateTable extends \BaseMigration {

	// name of the table to create
	protected $tablename = "sepamandate";

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sepamandate', function(Blueprint $table)
		{
			$this->up_table_generic($table);

			$table->integer('contract_id');
			$table->string('reference');
			$table->date('signature_date');
			// $table->enum('state', ['active', 'expired', 'cancelled', 'replaced']);
			$table->string('sepa_holder');
			$table->string('sepa_iban', 34);
			$table->string('sepa_bic', 11);
			$table->string('sepa_institute');
			$table->date('sepa_valid_from');
			$table->date('sepa_valid_to');
			$table->enum('type', ['first', 'recurring']);
		});
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
