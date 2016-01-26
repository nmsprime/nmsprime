<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnviaOrdersTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "enviaorders";


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

			$table->integer('orderid')->unsigned();
			$table->integer('ordertype_id')->unsigned();
			$table->string('ordertype');
			$table->integer('orderstatus_id')->unsigned();
			$table->string('orderstatus');
			$table->date('orderdate');
			$table->string('ordercomment');
			$table->string('customerreference', 60);
			$table->string('contractreference', 60);
			$table->string('localareacode');
			$table->string('baseno');
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
