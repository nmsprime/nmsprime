<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Updater to add flag for products of type internet to be bundled to voip
 *
 * @author Patrick Reichel
 */
class UpdateItemTableForFixedDatesFlags extends BaseMigration
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
            $table->boolean('valid_from_fixed')->after('valid_from')->default(false);
            $table->boolean('valid_to_fixed')->after('valid_to')->default(false);
        });

        // to not destroy dates of existing items we fix them if set
        // this is targeted to the productive system which should not lost data after merging the new master in
        DB::update('UPDATE item SET valid_from_fixed=TRUE WHERE valid_from IS NOT NULL;');
        DB::update('UPDATE item SET valid_to_fixed=TRUE WHERE valid_to IS NOT NULL;');
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
                'valid_from_fixed',
                'valid_to_fixed',
            ]);
        });
    }
}
