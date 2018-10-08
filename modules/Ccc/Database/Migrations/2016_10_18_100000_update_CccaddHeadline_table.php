<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateCccaddHeadlineTable extends BaseMigration
{
    protected $tablename = 'ccc';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('headline1');
            $table->string('headline2');
        });

        $this->set_fim_fields(['headline1', 'headline2']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('headline1');
            $table->dropColumn('headline2');
        });
    }
}
