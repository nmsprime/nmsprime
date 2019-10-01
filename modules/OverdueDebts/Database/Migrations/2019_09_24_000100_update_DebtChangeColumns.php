<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateDebtChangeColumns extends BaseMigration
{
    protected $tablename = 'debt';

    /**
     * As float is inaccurate the best way to store money amounts accurate is via decimal type
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->decimal('missing_amount', 10, 2)->nullable()->change();
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('bank_fee', 10, 2)->nullable()->change();
            $table->decimal('total_fee', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->float('missing_amount', 10, 4)->nullable()->change();
            $table->float('amount', 10, 4)->change();
            $table->float('bank_fee', 10, 4)->change();
            $table->float('total_fee', 10, 4)->change();
        });
    }
}
