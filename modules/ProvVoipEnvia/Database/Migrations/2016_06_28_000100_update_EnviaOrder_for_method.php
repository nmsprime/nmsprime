<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateEnviaOrderForMethod extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviaorder';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('method')->after('orderid')->nullable()->default(null);
            $table->datetime('last_user_interaction')->after('phonenumber_id')->nullable()->default(null);
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
            $table->dropColumn([
                'method',
                'last_user_interaction',
            ]);
        });
    }
}
