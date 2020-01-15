<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesHfcCustomer extends RelationshipFixes
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upFixRelationshipTables('mpr', ['netelement_id', 'prio_id', 'prio_before_id', 'prio_after_id']);
        $this->upFixRelationshipTables('mprgeopos', ['mpr_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->downFixRelationshipTables('mpr', ['netelement_id', 'prio_id', 'prio_before_id', 'prio_after_id']);
        $this->downFixRelationshipTables('mprgeopos', ['mpr_id']);
    }
}
