<?php

use Illuminate\Database\Schema\Blueprint;

class AddPropertyManagementRelationsToTicket extends BaseMigration
{
    protected $tablename = 'ticket';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable();
            $table->unsignedInteger('apartment_id')->nullable();
            $table->unsignedInteger('realty_id')->nullable();
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
                'contact_id',
                'apartment_id',
                'realty_id',
            ]);
        });
    }
}
