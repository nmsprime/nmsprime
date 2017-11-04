<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCccAuthuserMoveTable extends BaseMigration {

	protected $tablename = 'cccauthuser';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$main_db = getenv('DB_DATABASE');
		$ccc_host = getenv('CCC_DB_HOST');
		$ccc_db = getenv('CCC_DB_DATABASE');
		$ccc_user = getenv('CCC_DB_USERNAME');
		$ccc_pwd = getenv('CCC_DB_PASSWORD');

		DB::statement('CREATE DATABASE '.$ccc_db);

		DB::statement("CREATE USER '".$ccc_user."'@'".$ccc_host."' IDENTIFIED BY '".$ccc_pwd."'");
		DB::statement("GRANT select, delete, update, insert on ".$ccc_db.".* to '".$ccc_user."'@'".$ccc_host."'");

		Schema::rename($main_db.'.'.$this->tablename, $ccc_db.'.'.$this->tablename);

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$main_db = getenv('DB_DATABASE');
		$ccc_host = getenv('CCC_DB_HOST');
		$ccc_db = getenv('CCC_DB_DATABASE');
		$ccc_user = getenv('CCC_DB_USERNAME');
		$ccc_pwd = getenv('CCC_DB_PASSWORD');

		Schema::rename($ccc_db.'.'.$this->tablename, $main_db.'.'.$this->tablename);
		DB::statement("DROP USER '".$ccc_user."'@'".$ccc_host."'");
		DB::statement('DROP DATABASE '.$ccc_db);
	}

}
