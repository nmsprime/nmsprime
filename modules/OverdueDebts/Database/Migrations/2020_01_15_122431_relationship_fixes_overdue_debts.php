<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesOverdueDebts extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set 0 to NULL for debt
        Schema::table('debt', function (Blueprint $table) {
            $column = 'contract_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE debt SET `$column`=NULL WHERE `$column`=0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // set 0 to NULL for debt
        Schema::table('debt', function (Blueprint $table) {
            $column = 'contract_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE debt SET `$column`=0 WHERE `$column` IS NULL");
        });
    }
}
