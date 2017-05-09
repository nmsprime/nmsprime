<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnviaContractTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "enviacontract";


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


			$table->date('creation_date');
			$table->date('termination_date')->nullable()->default(NULL);
			$table->string('customer_reference', 60)->nullable()->default(NULL);
			$table->string('contract_reference', 60);
			$table->string('previous_contract_reference', 60)->nullable()->default(NULL);;
			$table->date('installation_address_change_date')->nullable()->default(NULL);
			$table->integer('contract_id');
			$table->integer('modem_id');

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
