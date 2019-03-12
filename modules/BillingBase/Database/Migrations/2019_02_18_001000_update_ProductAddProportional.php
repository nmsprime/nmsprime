<?php

use Illuminate\Database\Schema\Blueprint;
use Modules\BillingBase\Entities\Product;

/**
 * Add flag to calculate charges proportional
 *
 * @author Nino Ryschawy
 */
class UpdateProductAddProportional extends BaseMigration
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
            $table->boolean('proportional');
        });

        // Establish former implicit state
        Product::where('billing_cycle', 'Once')->update(['proportional' => 0]);
        Product::whereIn('billing_cycle', ['Monthly', 'Quarterly', 'Yearly'])->update(['proportional' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn(['proportional']);
        });
    }
}
