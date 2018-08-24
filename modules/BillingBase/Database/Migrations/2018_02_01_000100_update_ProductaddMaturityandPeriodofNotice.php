<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateProductaddMaturityandPeriodofNotice extends BaseMigration
{
    protected $tablename = 'product';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('period_of_notice', 20)->nullable(); 			// Kündigungsfrist
            // Change and Rename is not working here because of known Enum bug
            // $table->string('cycle_count')->change();
            // $table->renameColumn('cycle_count', 'maturity'); 		// Laufzeit (Tarif)
        });

        \DB::statement('ALTER TABLE product CHANGE cycle_count maturity VARCHAR(20)');
        \DB::table($this->tablename)->where('maturity', '=', 0)->update(['maturity' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn(['period_of_notice']);
        });

        \DB::statement('ALTER TABLE product CHANGE maturity cycle_count INTEGER NULL');
    }
}
