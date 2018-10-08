<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateItemIncreaseDecimalCountOfCreditAmount extends BaseMigration
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
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->decimal('credit_amount', 9, 4)->nullable()->change();
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
        // reverting the changes doesn't make sense
    }
}
