<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateEnviaOrderAddEnviacontractId extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviaorder';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('enviacontract_id')->after('modem_id')->nullable()->default(null);
        });

        // give all cols to be indexed (old and new ones => the index will be dropped and then
        // created from scratch)
        $this->set_fim_fields([
            'method',
            'ordertype',
            'orderstatus',
            'ordercomment',
            'customerreference',
            'contractreference',
        ]);
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
                'enviacontract_id',
            ]);
        });
    }
}
