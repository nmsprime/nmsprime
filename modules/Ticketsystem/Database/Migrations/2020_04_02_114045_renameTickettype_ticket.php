<?php

use Illuminate\Database\Schema\Blueprint;

class RenameTickettypeTicket extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickettype_ticket', function (Blueprint $table) {
            $table->renameColumn('tickettype_id', 'ticket_type_id');
        });

        Schema::rename('tickettype_ticket', 'ticket_type_ticket');
        Schema::rename('tickettype', 'ticket_type');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_type_ticket', function (Blueprint $table) {
            $table->renameColumn('ticket_type_id', 'tickettype_id');
        });

        Schema::rename('ticket_type_ticket', 'tickettype_ticket');
        Schema::rename('ticket_type', 'tickettype');
    }
}
