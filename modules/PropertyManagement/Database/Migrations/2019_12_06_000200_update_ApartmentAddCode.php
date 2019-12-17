<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateApartmentAddCode extends BaseMigration
{
    protected $tablename = 'apartment';

    /**
     * Run the migrations. Add relation of realties to contract for a group contract
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('code')->nullable();
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
            $table->dropColumn(['code']);
        });
    }
}
