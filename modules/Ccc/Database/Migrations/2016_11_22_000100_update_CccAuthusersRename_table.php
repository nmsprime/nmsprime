<?php

class UpdateCccAuthusersRenameTable extends BaseMigration
{
    protected $tablename = 'cccauthusers';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename($this->tablename, 'cccauthuser');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('cccauthuser', $this->tablename);
    }
}
