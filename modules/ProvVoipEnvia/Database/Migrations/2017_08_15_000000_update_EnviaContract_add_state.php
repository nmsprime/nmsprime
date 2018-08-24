<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateEnviaContractAddState extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviacontract';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('state', 60)->after('envia_contract_reference')->nullable()->default(null);
        });

        // give all cols to be indexed (old and new ones => the index will be dropped and then
        // created from scratch)
        $this->set_fim_fields([
            'envia_customer_reference',
            'envia_contract_reference',
            'state',
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
                'state',
            ]);
        });
    }
}
