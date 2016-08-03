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
		if($this->_db_is_local()) {
			// in local db case, we don't check for existing db,
			// as it should have been dropped in previous down()
			if($this->_voipmonitor_exists())
				$this->_create_db();
			else
				// disable module if db is local, but voipmonitor is not installed
				\PPModule::disable('voipmon');
		} else {
			if(!$this->_db_exists())
				// disable module if db is remote, but doesn't exist
				\PPModule::disable('voipmon');
		}

		return parent::up();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// should we enable the module here? probably not

		// only drop if db is local and it actually exists
		// remote db is assumed to be read-only
		if($this->_db_is_local()) {
			// stop voipmonitor, as we are about to drop its database
			$this->_voipmonitor_cmd('stop');
			$this->_drop_db_if_exists();
		}
	}

	protected function _create_db() {
		$this->_voipmonitor_cmd('stop');

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
		$name = \DB::connection($this->connection)->getDatabaseName();
		// See renameColumn() removed: https://github.com/doctrine/dbal/blob/master/UPGRADE.md
		\DB::connection($this->connection)->statement('ALTER TABLE '.$name.'.'.$this->tablename.' CHANGE COLUMN `ID` `id` bigint unsigned auto_increment;');

		$this->_voipmonitor_cmd('start');
	}

	protected function _db_exists()
	{
		try {
			$name = \DB::connection($this->connection)->getDatabaseName();
			\DB::connection($this->connection)->select("SHOW DATABASES LIKE '$name'");
		}
		catch (PDOException $e) {
			// Code 1049 == Unknown database '%s'
			if($e->getCode() == 1049)
				return false;
			// Don't catch other PDOExceptions
			throw $e;
		}
		return true;
	}

	protected function _db_is_local()
	{
		$host = \DB::connection($this->connection)->getConfig('host');
		// check if host is either localhost, 127.0.0.0/8 (ipv4) or ::1 (ipv6)
		return $host == 'localhost' || substr($host,0,4) == '127.' || $host == '::1' || $host == '0:0:0:0:0:0:0:1';
	}

	protected function _drop_db_if_exists()
	{
		if(!$this->_db_exists())
			return;
		$name = \DB::connection($this->connection)->getDatabaseName();
		\DB::connection($this->connection)->statement("DROP DATABASE $name");
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

		if($ret)
			echo("voipmonitor currently not installed\n");

		return !$ret;
	}

	protected function _voipmonitor_cmd($cmd)
	{
		$file = storage_path('systemd/voipmonitor');

		// don't do anything, if voipmonitor is not installed
		if(!$this->_voipmonitor_exists())
			return;

		system("echo $cmd > $file");
		// $file will be removed by nmsd
		while(file_exists($file))
			// sleep to reduce busy-waiting load
			sleep(1);
	}

}
