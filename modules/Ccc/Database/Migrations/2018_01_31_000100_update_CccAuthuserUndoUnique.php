<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCccAuthuserUndoUnique extends BaseMigration
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
        // Get the Key name from Database to definitely remove the key successfully
        // before the migration the key is called 'cccauthusers_login_name_unique' ... after rollback it's called 'cccauthuser_login_name_unique'
        $ret = \DB::connection($this->connection)->select(\DB::raw('SHOW keys from '.$this->tablename.' where Column_name=\'login_name\''));

        if (! $ret) {
            return;
        }

        $key = $ret[0]->Key_name;

        Schema::connection($this->connection)->table($this->tablename, function (Blueprint $table) use ($key) {
            $table->dropUnique($key);
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
            // Attention: This will create a unique key called 'cccauthuser_login_name_unique' (without 's')
            $table->unique('login_name');
        });
    }
}
