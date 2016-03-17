<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "price";

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
			$table->enum('type', ['Internet', 'TV', 'Voip', 'Device', 'Credit', 'Mixed', 'Other']);
			$table->integer('qos_id')->unsigned()->nullable();
			$table->enum('voip_tariff', [0 => '', 1 => 'Flat', 2 => 'Basic']);
			$table->enum('billing_cycle', ['Monthly', 'Once', 'Yearly']);
			$table->integer('costcenter_id')->unsigned();
			$table->float('price');
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
		Schema::drop($this->tablename);
	}

}