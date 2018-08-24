<?php

use Illuminate\Database\Schema\Blueprint;

class CreateSettlementRunTable extends BaseMigration
{
    protected $tablename = 'settlementrun';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->string('path');
            $table->string('description');
            $table->boolean('verified'); 	// termination of items only allowed on last days of month
        });

        $this->set_fim_fields(['path', 'description']);
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
