<?php

/**
 * Change item counts from zero to one, as zero count is not possible (anymore)
 *
 * @author Nino Ryschawy
 */
class UpdateItemCorrectCount extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('item')->where('count', 0)->update(['count' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
