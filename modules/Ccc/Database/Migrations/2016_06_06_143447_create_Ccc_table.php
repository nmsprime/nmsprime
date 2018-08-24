<?php

use Illuminate\Database\Schema\Blueprint;

class CreateCccTable extends BaseMigration
{
    protected $tablename = 'ccc';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('template_filename');
        });

        DB::update('INSERT INTO '.$this->tablename.' (template_filename) VALUES("");');
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
