<?php

use Illuminate\Database\Schema\Blueprint;

class CreateSalesmanTable extends BaseMigration
{
    protected $tablename = 'salesman';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('firstname');
            $table->string('lastname');
            $table->float('commission');
            $table->string('products');
            $table->string('description');
        });

        $this->set_fim_fields(['firstname', 'lastname', 'description']);
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
