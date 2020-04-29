<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * Seems that 2020_01_13_110520_set_empty_strings_to_null_ccc.php did not affect table cccauthuser (due to missing/wrong connection?)
 * We fix the problem manually and explicitely here…
 *
 * @author Patrick Reichel
 */
class UpdateCccAuthuserMakeNameFieldsNullable extends BaseMigration
{
    protected $tablename = 'cccauthuser';
    protected $connection = 'mysql-ccc';

    // the rows to be nullable (firstname/lastname in contract can be null if type is institutional)
    protected $cols = [
        'first_name',
        'last_name',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->table($this->tablename, function (Blueprint $table) {
            foreach ($this->cols as $col) {
                $table->string($col)->nullable()->change();
            }
        });
        foreach ($this->cols as $col) {
            DB::connection($this->connection)->update("UPDATE $this->tablename SET $col=NULL WHERE $col=''");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->cols as $col) {
            DB::connection($this->connection)->update("UPDATE $this->tablename SET $col='' WHERE $col IS NULL");
        }
        Schema::connection($this->connection)->table($this->tablename, function (Blueprint $table) {
            foreach ($this->cols as $col) {
                $table->string($col)->change();
            }
        });
    }
}
