<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdrTable extends BaseMigration {

	// Default config of the voipmonitor daemon is to create its own database, use it instead of the default db
	protected $connection = 'mysql-voipmonitor';
	// Name of the table
	protected $tablename = 'cdr';

	/**
	 * Run the migrations.
	 *
	 * @author Ole Ernst
	 *
	 * @return void
	 */
	public function up()
	{
		if(!$this->_voipmonitor_exists())
			return parent::up();

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

		// Name of the database accessed through $this->connection
		$database = Schema::connection($this->connection)->getConnection()->getConfig('database');
		// See renameColumn() removed: https://github.com/doctrine/dbal/blob/master/UPGRADE.md
		\DB::statement('ALTER TABLE '.$database.'.'.$this->tablename.' CHANGE COLUMN `ID` `id` bigint unsigned auto_increment;');
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

	/**
	 * Check if voipmonitor application is installed on the system
	 *
	 * @author Ole Ernst
	 *
	 * @return True if voipmonitor application is installed on the system, False else
	 */
	protected function _voipmonitor_exists()
	{
		system('which voipmonitor > /dev/null 2>&1', $ret);

		if($ret) {
			echo("voipmonitor currently not available, refresh migration once it is installed by:\n");
			echo("\tphp artisan module:migrate-refresh VoipMon\n");
			return false;
		}

		return true;
	}

}