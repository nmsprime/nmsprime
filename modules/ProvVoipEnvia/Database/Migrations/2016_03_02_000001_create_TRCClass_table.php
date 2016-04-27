<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTRCClassTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "trcclass";


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

			$table->integer('trc_id')->unsigned()->unique();
			$table->string('trc_short');
			$table->string('trc_description');
		});

		// hardcode the TRC classes to database table
		// TODO: should we outsource this?
		// IMHO this seems to be fixed; so I put it in the migration…
		$trc_data = array(
			array(0, 'TRC0', 'Keine Verkehrseinschränkung'),
			array(1, 'TRC1', 'Sperre (0)900, 0087, 0088'),
			array(2, 'TRC2', 'Sperre (0)900'),
			array(3, 'TRC3', 'Sperre 0900, 0087, 0088, 0137, 019x, 118xy, 0181-0189'),
			array(4, 'TRC4', 'Sperre 00x (internationale Verbindungen)'),
			array(5, 'TRC5', 'Sperre (0)137, (0)138'),
			array(6, 'TRC6', 'Sperre Mobilfunknetze national'),
			array(8, 'TRC8', 'Sperre (0)900, 00x'),
			array(9, 'TRC9', 'Sperre (0)900, (0)137'),
			array(11, 'TRC11', 'Sperre 0087, 0088 (Satellitenfunk)'),
			array(12, 'TRC12', 'Sperre (0)900, 0087, 0088, (0)137, (0)180'),
			array(13, 'TRC13', 'Sperre (0)900, 00x, (0)137, 0180, 018, 118'),
			array(14, 'TRC14', 'nur nationales Festnetz'),
			array(15, 'TRC15', 'Sperre International und Servicerufnummern'),
			array(16, 'TRC16', 'Sperre International und nationales Mobilfunknetz (00x, 0088, 0087, Mobilfunknetze)'),
			array(17, 'TRC17', 'Sperre Servicerufnummern und nationales Mobilfunknetz (0900, 0137,0138, 0180, 018, 118, 0088, 0087, Mobilfunknetze'),
			array(18, 'TRC18', 'Sperre 0087, 0088, 0900, 0180'),
			array(19, 'TRC19', 'Sperre 0087,0088,0137, 0138, 0900 und Mobilfunknetze'),
			array(20, 'TRC20', 'Sperre (0)900, 00x, (0)137,(0)138'),
		);

		foreach ($trc_data as $trc) {
			DB::update("INSERT INTO ".$this->tablename." (trc_id, trc_short, trc_description) VALUES($trc[0], '$trc[1]', '$trc[2]');");
		};

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
