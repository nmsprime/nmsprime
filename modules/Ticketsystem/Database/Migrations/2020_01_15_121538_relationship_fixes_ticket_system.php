<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class RelationshipFixesTicketSystem extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // set 0 to NULL for ticket
        Schema::table('ticket', function (Blueprint $table) {
            $column = 'contract_id';
            $table->unsignedInteger($column)->nullable()->change();
            DB::statement("UPDATE ticket SET `$column`=NULL WHERE `$column`=0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // set 0 to NULL for ticket
        Schema::table('ticket', function (Blueprint $table) {
            $column = 'contract_id';
            $table->unsignedInteger($column)->change();
            DB::statement("UPDATE ticket SET `$column`=0 WHERE `$column` IS NULL");
        });
    }
}
