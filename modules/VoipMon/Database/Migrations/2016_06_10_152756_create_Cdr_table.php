<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdrTable extends BaseMigration {

	// Default config of the voipmonitor daemon is to create its own database, use it instead of db_lara
	protected $connection = 'mysql-voipmonitor';
	// name of the table to create
	protected $tablename = 'cdr';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Let voipmonitor create the basic database for us
		system('voipmonitor --config-file /etc/voipmonitor.conf --update-schema');
		// We just append the column we need
		Schema::connection($this->connection)->table($this->tablename, function(Blueprint $table)
		{
			// We can't use up_table_generic($table), as existing table already contains an id column
			$table->timestamps();
			$table->softDeletes();
			// Add link to phonenumbers
			$table->integer('phonenumber_id')->unsigned()->nullable();
		});
		// See renameColumn() removed: https://github.com/doctrine/dbal/blob/master/UPGRADE.md
		\DB::statement('ALTER TABLE voipmonitor.cdr CHANGE COLUMN `ID` `id` bigint unsigned auto_increment;');

		return parent::up();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection($this->connection)->drop($this->tablename);
	}

}