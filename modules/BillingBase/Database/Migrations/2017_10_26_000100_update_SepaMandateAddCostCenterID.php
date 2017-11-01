<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSepaMandateAddCostCenterID extends BaseMigration {

	// name of the table to create
	protected $tablename = "sepamandate";

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table($this->tablename, function(Blueprint $table)
		{
			$table->integer('costcenter_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table($this->tablename, function(Blueprint $table)
		{
			$table->dropColumn('costcenter_id');
		});
	}

}
