<?php

use Illuminate\Database\Schema\Blueprint;

class CreateItemTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('contract_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->tinyInteger('count')->unsigned()->default(1);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->float('credit_amount')->nullable();
            $table->integer('costcenter_id')->unsigned();
            $table->text('accounting_text');
            $table->tinyInteger('payed_month');			// payed already this year for yearly items - because billing month can change
        });

        $this->set_fim_fields(['accounting_text']);

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
