<?php

use Illuminate\Database\Schema\Blueprint;

class ChangeFloatToDecimal extends BaseMigration
{
    /**
     * As float is inaccurate the best way to store money amounts accurate is via decimal type
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item', function (Blueprint $table) {
            $table->decimal('credit_amount', 10, 4)->nullable()->change();
        });

        Schema::table('product', function (Blueprint $table) {
            $table->decimal('price', 10, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item', function (Blueprint $table) {
            $table->float('credit_amount')->nullable()->change();
        });

        Schema::table('product', function (Blueprint $table) {
            $table->float('price', 10, 4)->nullable()->change();
        });
    }
}
