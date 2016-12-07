<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/*
 * MPR: Modem Positioning Rule
 */
class UpdateMprTableRenameTree extends BaseMigration {

	// name of the table to create
	protected $tablename = "mpr";

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table($this->tablename, function(Blueprint $table)
		{
			$table->renameColumn('tree_id', 'netelement_id');
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
		Schema::table($this->tablename, function(Blueprint $table)
		{
			$table->renameColumn('netelement_id', 'tree_id');
		});
	}

}
