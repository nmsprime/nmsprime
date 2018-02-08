<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use Modules\ProvBase\Entities\Cmts;

class CreateCmtsTable extends BaseMigration {

	// name of the table to create
	protected $tablename = "cmts";


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

			$table->string('hostname');
			$table->string('type');
			$table->string('ip');		// bundle ip
			$table->string('community_rw');
			$table->string('community_ro');
			$table->string('company');
			$table->integer('network');
			$table->integer('state');
			$table->integer('monitoring');
		});

		$this->set_fim_fields(['hostname', 'type', 'ip', 'community_ro', 'community_rw', 'company']);

		// add fulltext index for all given fields
		// TODO: remove ?
		if (isset($this->index) && (count($this->index) > 0))
			DB::statement("CREATE FULLTEXT INDEX ".$this->tablename."_all ON ".$this->tablename." (".implode(', ', $this->index).")");

		return parent::up();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$c = Cmts::first();
		if ($c)
			$c->del_cmts_includes();

		Schema::drop($this->tablename);

		// remove all through dhcpCommand created cmts config files
		$files = glob('/etc/dhcp/nmsprime/cmts_gws/*');		// get all files in dir
		foreach ($files as $file)
		{
			if(is_file($file))
				unlink($file);
		}
	}

}
