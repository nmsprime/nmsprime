<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateNumberRangeSetDefaults extends BaseMigration
{
    // name of the table to change
    protected $tablename = 'numberrange';

    /**
     * Run the migrations.
     * Because of new validation rules birthday can now be NULL.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            DB::statement("ALTER TABLE $this->tablename MODIFY COLUMN prefix varchar(191) not null DEFAULT ''");
            DB::statement("ALTER TABLE $this->tablename MODIFY COLUMN suffix varchar(191) not null DEFAULT ''");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            DB::statement("ALTER TABLE $this->tablename MODIFY COLUMN prefix varchar(191) null DEFAULT null");
            DB::statement("ALTER TABLE $this->tablename MODIFY COLUMN suffix varchar(191) null DEFAULT null");
        });
    }
}
