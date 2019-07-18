<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateSettlementRunAddUploadTimestamp extends BaseMigration
{
    protected $tablename = 'settlementrun';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->timestamp('uploaded_at')->nullable()->after('deleted_at');
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
            $table->dropColumn('uploaded_at');
        });
    }
}
