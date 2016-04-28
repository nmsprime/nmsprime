<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnviaOrderTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "enviaorder";


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

			$table->integer('orderid')->unsigned()->unique();
			$table->integer('ordertype_id')->unsigned()->nullable()->default(NULL);
			$table->string('ordertype')->nullable()->default(NULL);
			$table->integer('orderstatus_id')->unsigned()->nullable()->default(NULL);
			$table->string('orderstatus')->nullable()->default(NULL);
			$table->date('orderdate')->nullable()->default(NULL);
			$table->string('ordercomment')->nullable()->default(NULL);
			$table->integer('related_order_id')->unsigned()->nullable()->default(NULL);
			$table->string('customerreference', 60)->nullable()->default(NULL);
			$table->string('contractreference', 60)->nullable()->default(NULL);
			$table->integer('contract_id')->nullable()->default(NULL);
			$table->integer('phonenumber_id')->nullable()->default(NULL);
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
