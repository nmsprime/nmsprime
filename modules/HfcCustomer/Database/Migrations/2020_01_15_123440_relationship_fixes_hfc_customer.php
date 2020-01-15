<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesHfcCustomer extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set 0 to NULL for mpr
        Schema::table('mpr', function (Blueprint $table) {
            foreach (['netelement_id', 'prio_id', 'prio_before_id', 'prio_after_id'] as $column) {
                $table->unsignedInteger($column)->nullable()->change();
                DB::statement("UPDATE mpr SET `$column`=NULL WHERE `$column`=0");
            }
        });

        // set 0 to NULL for mprgeopos
        Schema::table('mprgeopos', function (Blueprint $table) {
            $column = 'mpr_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE mprgeopos SET `$column`=NULL WHERE `$column`=0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // set NULL ro 0 for mpr
        Schema::table('mpr', function (Blueprint $table) {
            foreach (['netelement_id', 'prio_id', 'prio_before_id', 'prio_after_id'] as $column) {
                $table->unsignedInteger($column)->nullable()->change();
                DB::statement("UPDATE mpr SET `$column`=0 WHERE `$column` is NULL");
            }
        });

        // set 0 to NULL for mprgeopos
        Schema::table('mprgeopos', function (Blueprint $table) {
            $column = 'mpr_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE mprgeopos SET `$column`=0 WHERE `$column` is NULL");
        });
    }
}
