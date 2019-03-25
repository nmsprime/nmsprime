<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddTicketConfigToGlobalConfig extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_config', function (Blueprint $table) {
            $table->string('noReplyMail');
        });

        Schema::table('global_config', function (Blueprint $table) {
            $table->string('noReplyName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_config', function (Blueprint $table) {
            $table->dropColumn('noReplyMail');
        });

        Schema::table('global_config', function (Blueprint $table) {
            $table->dropColumn('noReplyName');
        });
    }
}
