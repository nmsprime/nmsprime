<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateEnviaOrderForMethod extends BaseMigration {

	// name of the table to create
	protected $tablename = "enviaorder";


	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table($this->tablename, function(Blueprint $table) {

			$table->string('method')->after('orderid')->nullable()->default(NULL);
		});

	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
			$table->dropColumn([
				'method',
			]);
	}

}
