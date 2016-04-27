<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostCenterTable extends BaseMigration {

	protected $tablename = 'costcenter';

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
			$table->string('number');
			$table->integer('sepa_account_id');
			$table->tinyInteger('billing_month');
			$table->string('invoice_headline');
			$table->string('description');
		});

		$this->set_fim_fields(['name', 'description']);

		Schema::table('contract', function(Blueprint $table)
		{
			$table->integer('costcenter_id');
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
			$table->dropColumn('costcenter_id');
		});
	}

}
