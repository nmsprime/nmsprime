<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for products of type internet to be bundled to voip
 *
 * @author Patrick Reichel
 */
class UpdateProductTableForBundledFlag extends BaseMigration
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
            $table->boolean('bundled_with_voip')->default(false);
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
            $table->dropColumn([
                'bundled_with_voip',
            ]);
        });
    }
}
