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


			$table->datetime('external_creation_date')->nullable()->default(NULL);
			$table->datetime('external_termination_date')->nullable()->default(NULL);
			$table->string('envia_customer_reference', 60)->nullable()->default(NULL);
			$table->string('envia_contract_reference', 60)->nullable()->default(NULL);
			$table->date('start_date')->nullable()->default(NULL);
			$table->date('end_date')->nullable()->default(NULL);

			// Envia contract reference can change over the time (e.g. on contract_relocate)
			$table->integer('next_id')->unsigned()->nullable()->default(NULL);
			$table->integer('prev_id')->unsigned()->nullable()->default(NULL);
			$table->string('end_reason', 60)->nullable()->default(NULL); // API method that ends this contract

			// this are some contract related configuration fields ⇒ watch API description for details
			$table->integer('lock_level')->unsigned()->nullable()->default(NULL);
			$table->enum('method', array('MGCP', 'SIP'))->default('SIP');	// ATM there is only SIP implemented
			$table->integer('sla_id')->unsigned()->nullable()->default(NULL);
			$table->integer('tariff_id')->unsigned()->nullable()->default(NULL);
			$table->integer('variation_id')->unsigned()->nullable()->default(NULL);

			// relations of this envia contract
			// they are also related to
			//	phonenumbers ⇒ this will be stored as enviacontract_id in phonenumbermanagement
			//	enviaorders ⇒ this will be stored as enviacontract_id in enviaorder
			$table->integer('contract_id')->unsigned()->nullable()->default(NULL);
			$table->integer('modem_id')->unsigned()->nullable()->default(NULL);

		});

		$this->set_fim_fields([
			'envia_customer_reference',
			'envia_contract_reference',
		]);

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
