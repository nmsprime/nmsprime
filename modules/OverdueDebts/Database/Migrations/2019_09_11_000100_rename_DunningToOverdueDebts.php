<?php

use Illuminate\Database\Schema\Blueprint;

class RenameDunningToOverdueDebts extends BaseMigration
{
    protected $tablename = 'dunning';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('dunning', 'overduedebts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('overduedebts', 'dunning');
    }
}
