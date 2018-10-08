<?php

use Illuminate\Database\Schema\Blueprint;

class CreateEnviaOrderTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviaorder';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('orderid')->unsigned()->unique();
            $table->integer('ordertype_id')->unsigned()->nullable()->default(null);
            $table->string('ordertype')->nullable()->default(null);
            $table->integer('orderstatus_id')->unsigned()->nullable()->default(null);
            $table->string('orderstatus')->nullable()->default(null);
            $table->date('orderdate')->nullable()->default(null);
            $table->string('ordercomment')->nullable()->default(null);
            $table->integer('related_order_id')->unsigned()->nullable()->default(null);
            $table->string('customerreference', 60)->nullable()->default(null);
            $table->string('contractreference', 60)->nullable()->default(null);
            $table->integer('contract_id')->nullable()->default(null);
            $table->integer('phonenumber_id')->nullable()->default(null);
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
