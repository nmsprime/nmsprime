<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesmanTable extends BaseMigration {

	protected $tablename = 'salesman';

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
			
			$table->string('firstname');
			$table->string('lastname');
			$table->float('commission');
			$table->string('products');
			$table->string('description');
		});

		$this->set_fim_fields(['firstname', 'lastname', 'description']);

		Schema::table('contract', function(Blueprint $table)
		{
			$table->integer('salesman_id');
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop($this->tablename);

		Schema::table('contract', function(Blueprint $table)
		{
			$table->dropColumn('salesman_id');
		});
	}

}
