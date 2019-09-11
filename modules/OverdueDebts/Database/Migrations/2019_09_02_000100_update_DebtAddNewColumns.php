<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateDebtAddNewColumns extends BaseMigration
{
    protected $tablename = 'debt';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('parent_id')->nullable();
            $table->float('missing_amount', 10, 4)->nullable();

            // Make column nullable
            $table->boolean('indicator')->nullable()->change();
        });

        DB::table('debt')->where('indicator', 0)->update(['indicator' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'missing_amount']);
        });
    }
}
