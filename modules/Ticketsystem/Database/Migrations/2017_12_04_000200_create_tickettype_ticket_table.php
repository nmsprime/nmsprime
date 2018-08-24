<?php

use Illuminate\Database\Schema\Blueprint;

class CreateTicketTypeTicketTable extends BaseMigration
{
    protected $tablename = 'tickettype_ticket';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->nullable();

            $table->integer('tickettype_id')->nullable();
            $table->integer('ticket_id')->nullable();

            return parent::up();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tablename);
    }
}
