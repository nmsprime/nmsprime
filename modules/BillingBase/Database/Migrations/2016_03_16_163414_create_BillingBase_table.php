<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingBaseTable extends BaseMigration {

	protected $tablename = 'billingbase';

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
			
			$table->date('rcd');   // requested collection date (Zahlungsziel)
			$table->enum('currency', ['EUR', 'USD']);
			$table->float('tax');
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
	}

}
