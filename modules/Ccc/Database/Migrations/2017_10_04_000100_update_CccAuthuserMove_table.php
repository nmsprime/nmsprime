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
		$faker = Faker\Factory::create();
		$psw = str_replace(["\\", "'"], ';', $faker->password);

		DB::statement('CREATE DATABASE lara_customer');
		DB::statement("CREATE USER 'customer'@'localhost' IDENTIFIED BY '$psw'");
		DB::statement("GRANT select, delete, update, insert on lara_customer.* to 'customer'@'localhost'");

		Schema::rename('db_lara.'.$this->tablename, 'lara_customer.cccauthuser');

		$login_data = "\nCCC_DB_DATABASE=lara_customer\nCCC_DB_USERNAME=customer\nCCC_DB_PASSWORD='$psw'";
		File::append('.env', $login_data);

		echo "TODO: See .env File and check if CCC DATABASE Login Data is properly set! (only once)\n";
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::rename('lara_customer.cccauthuser', 'db_lara.'.$this->tablename);
		DB::statement("DROP USER 'customer'@'localhost'");
		DB::statement('DROP DATABASE lara_customer');

		// remove all CCC Entries from .env
		$arr = file('.env');

		foreach ($arr as $key => $value) {
			if (strpos($value, 'CCC_DB_DATABASE') === false &&
				strpos($value, 'CCC_DB_USERNAME') === false &&
				strpos($value, 'CCC_DB_PASSWORD') === false)
				$data[] = $value;
		}

		File::put('.env', $data);
	}

}
