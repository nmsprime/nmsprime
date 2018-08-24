<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for products of type internet to be bundled to voip
 *
 * @author Patrick Reichel
 */
class UpdateBillingBaseTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'billingbase';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->enum('userlang', ['de', 'en'])->default('de');
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
                'userlang',
            ]);
        });
    }
}
