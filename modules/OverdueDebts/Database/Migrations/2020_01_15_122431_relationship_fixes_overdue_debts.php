<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesOverdueDebts extends RelationshipFixes
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upFixRelationshipTables('debt', ['contract_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->downFixRelationshipTables('debt', ['contract_id']);
    }
}
