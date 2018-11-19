<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add minimum maturity (german: Laufzeit) to products
 *
 * @author Nino Ryschawy
 */
class UpdateProductAddMaturityMin extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'product';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('maturity_min', 20)->nullable();
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
            $table->dropColumn(['maturity_min']);
        });
    }
}
