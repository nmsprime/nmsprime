<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateCccAuthuserSetNullableFields extends BaseMigration
{
    protected $tablename = 'cccauthuser';
    protected $connection = 'mysql-ccc';

    /**
     * Run the migrations.
     *
     * Remove unique key as this conflicts with softdeletes (when a contract is deleted and added with same number again)
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->table($this->tablename, function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->table($this->tablename, function (Blueprint $table) {
            $table->string('email')->change();
        });
    }
}
