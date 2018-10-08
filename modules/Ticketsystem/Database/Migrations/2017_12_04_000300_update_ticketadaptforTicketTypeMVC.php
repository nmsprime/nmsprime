<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateTicketadaptforTicketTypeMVC extends BaseMigration
{
    protected $tablename = 'ticket';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('type');
            $table->timestamp('duedate')->nullable(); 			// Fälligkeitsdatum

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
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('duedate');
            $table->enum('type', ['General', 'Technical', 'Accounting']);
        });
    }
}
