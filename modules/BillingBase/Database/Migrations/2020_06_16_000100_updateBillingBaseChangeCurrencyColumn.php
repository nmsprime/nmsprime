<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateBillingBaseChangeCurrencyColumn extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billingbase', function (Blueprint $table) {
            $table->string('currency')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billingbase', function (Blueprint $table) {
            DB::statement("ALTER TABLE billingbase CHANGE currency currency ENUM('USD', 'EUR')");
        });
    }
}
