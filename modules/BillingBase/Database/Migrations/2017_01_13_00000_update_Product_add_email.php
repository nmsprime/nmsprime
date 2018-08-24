<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateProductAddEmail extends BaseMigration
{
    protected $tablename = 'product';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('email_count')->unsigned();
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
                'email_count',
            ]);
        });
    }
}
