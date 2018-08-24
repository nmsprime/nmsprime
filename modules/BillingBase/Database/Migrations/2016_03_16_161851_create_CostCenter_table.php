<?php

use Illuminate\Database\Schema\Blueprint;

class CreateCostCenterTable extends BaseMigration
{
    protected $tablename = 'costcenter';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('name');
            $table->string('number');
            $table->integer('sepa_account_id');
            $table->tinyInteger('billing_month');
            $table->string('description');
        });

        $this->set_fim_fields(['name', 'number', 'description']);
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
