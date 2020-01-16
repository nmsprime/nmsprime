<?php

class RelationshipFixesBillingBase extends RelationshipFixes
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upFixRelationshipTables('costcenter', ['sepaaccount_id']);
        $this->upFixRelationshipTables('invoice', ['settlementrun_id', 'sepaaccount_id']);
        $this->upFixRelationshipTables('item', ['costcenter_id']);
        $this->upFixRelationshipTables('numberrange', ['costcenter_id']);
        $this->upFixRelationshipTables('product', ['costcenter_id']);
        $this->upFixRelationshipTables('sepaaccount', ['company_id']);
        $this->upFixRelationshipTables('contract', ['salesman_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->downFixRelationshipTables('costcenter', ['sepaaccount_id']);
        $this->downFixRelationshipTables('invoice', ['settlementrun_id', 'sepaaccount_id']);
        $this->downFixRelationshipTables('item', ['costcenter_id']);
        $this->downFixRelationshipTables('numberrange', ['costcenter_id']);
        $this->downFixRelationshipTables('product', ['costcenter_id']);
        $this->downFixRelationshipTables('sepaaccount', ['company_id']);
        $this->downFixRelationshipTables('contract', ['salesman_id']);
    }
}
