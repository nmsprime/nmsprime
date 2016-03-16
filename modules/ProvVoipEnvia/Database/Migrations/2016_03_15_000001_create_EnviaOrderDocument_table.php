<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnviaOrderDocumentTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "enviaorderdocument";


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

			$table->enum('document_type', [
				'Auftrag',
				'Insolvenz',
				'Kommunikation',
				'NachweisUnternehmer',
				'Portierungsformblatt',
				'Telefonbucheintrag',
				'Vertrag',
				'Vertragsbeend',
				'Vollmacht',
			]);
			$table->string('filename');
			$table->integer('enviaorder_id');
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

