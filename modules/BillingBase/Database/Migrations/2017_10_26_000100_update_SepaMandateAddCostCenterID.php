<?php

use Digitick\Sepa\PaymentInformation;
use Illuminate\Database\Schema\Blueprint;

class UpdateSepaMandateAddCostCenterID extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'sepamandate';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('costcenter_id')->nullable();
            $table->boolean('disable')->nullable(); 			// temporary disabled mandate flag
            $table->text('description')->nullable();

            DB::statement("ALTER TABLE $this->tablename CHANGE COLUMN state state ENUM('".PaymentInformation::S_FIRST."', '".PaymentInformation::S_RECURRING."', '".PaymentInformation::S_ONEOFF."', '".PaymentInformation::S_FINAL."')");
            $table->dropColumn('recurring');
        });

        $this->set_fim_fields(['description', 'sepa_bic', 'sepa_iban', 'sepa_institute', 'reference', 'sepa_holder']);

        DB::update("UPDATE $this->tablename SET state='".PaymentInformation::S_RECURRING."' where (state is null or state='' or state='RECUR');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            // NOTE: we dont need to undo changes of column "state" as it wasn't used before

            $table->dropColumn(['costcenter_id', 'disable', 'description']);
            $table->boolean('recurring');
        });
    }
}
