<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Add Indexes to Item & SepaMandate table to improve performance
 *
 * @author Nino Ryschawy
 */
class UpdateTablesAddIndex extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item', function (Blueprint $table) {
            $table->index('product_id', 'by_product_id');
        });

        Schema::table('sepamandate', function (Blueprint $table) {
            $table->index('contract_id', 'by_contract_id');
            $table->index('costcenter_id', 'by_costcenter_id');
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
            $table->dropIndex('by_product_id');
        });

        Schema::table('sepamandate', function (Blueprint $table) {
            $table->dropIndex('by_contract_id');
            $table->dropIndex('by_costcenter_id');
        });
    }
}
