<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesBillingBase extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set 0 to NULL for costcenter
        Schema::table('costcenter', function (Blueprint $table) {
            $column = 'sepaaccount_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE costcenter SET `$column`=NULL WHERE `$column`=0");
        });

        // set 0 to NULL for invoice
        Schema::table('invoice', function (Blueprint $table) {
            foreach (['settlementrun_id', 'sepaaccount_id'] as $column) {
                $table->unsignedInteger($column)->nullable()->change();
                DB::statement("UPDATE invoice SET `$column`=NULL WHERE `$column`=0");
            }
        });

        // set 0 to NULL for item
        Schema::table('item', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE item SET `$column`=NULL WHERE `$column`=0");
        });

        // set 0 to NULL for numberrange
        Schema::table('numberrange', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE numberrange SET `$column`=NULL WHERE `$column`=0");
        });

        // set 0 to NULL for product
        Schema::table('product', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE product SET `$column`=NULL WHERE `$column`=0");
        });

        // set 0 to NULL for sepaaccount
        Schema::table('sepaaccount', function (Blueprint $table) {
            $column = 'company_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE sepaaccount SET `$column`=NULL WHERE `$column`=0");
        });

        // set 0 to NULL for contract
        Schema::table('contract', function (Blueprint $table) {
            $column = 'salesman_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE `contract` SET `$column`=NULL WHERE `$column`=0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // set NULL to 0 for costcenter
        Schema::table('costcenter', function (Blueprint $table) {
            $column = 'sepaaccount_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE costcenter SET `$column`=0 WHERE `$column` IS NULL");
        });

        // set NULL to 0 for invoice
        Schema::table('invoice', function (Blueprint $table) {
            foreach (['settlementrun_id', 'sepaaccount_id'] as $column) {
                $table->unsignedInteger($column)->change();
                DB::statement("UPDATE invoice SET `$column`=0 WHERE `$column` IS NULL");
            }
        });

        // set NULL to 0 for item
        Schema::table('item', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE item SET `$column`=0 WHERE `$column` IS NULL");
        });

        // set NULL to 0 for numberrange
        Schema::table('numberrange', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE numberrange SET `$column`=0 WHERE `$column` IS NULL");
        });

        // set NULL to 0 for product
        Schema::table('product', function (Blueprint $table) {
            $column = 'costcenter_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE product SET `$column`=0 WHERE `$column` IS NULL");
        });

        // set NULL to 0 for sepaaccount
        Schema::table('sepaaccount', function (Blueprint $table) {
            $column = 'company_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE sepaaccount SET `$column`=0 WHERE `$column` IS NULL");
        });

        // set NULL ro 0 for contract
        Schema::table('contract', function (Blueprint $table) {
            $column = 'salesman_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE `contract` SET `$column`=0 WHERE `$column` is NULL");
        });
    }
}
