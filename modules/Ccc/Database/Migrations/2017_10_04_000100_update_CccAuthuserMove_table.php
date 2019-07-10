<?php

class UpdateCccAuthuserMoveTable extends BaseMigration
{
    protected $tablename = 'cccauthuser';
    protected $connection = 'mysql-root';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $main_db = getenv('DB_DATABASE');
        $ccc_db = getenv('CCC_DB_DATABASE');

        Schema::connection($this->connection)->rename($main_db.'.'.$this->tablename, $ccc_db.'.'.$this->tablename);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $main_db = getenv('DB_DATABASE');
        $ccc_db = getenv('CCC_DB_DATABASE');

        Schema::connection($this->connection)->rename($ccc_db.'.'.$this->tablename, $main_db.'.'.$this->tablename);
    }
}
