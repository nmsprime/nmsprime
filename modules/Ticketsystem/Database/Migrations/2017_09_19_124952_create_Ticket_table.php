<?php

use Illuminate\Database\Schema\Blueprint;

class CreateTicketTable extends BaseMigration
{
    protected $tablename = 'ticket';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('name');
            $table->text('description');
            $table->integer('user_id');
            $table->integer('contract_id');
            $table->enum('state', ['New', 'In process', 'Closed']);
            $table->enum('type', ['General', 'Technical', 'Accounting']);
            $table->enum('priority', ['Trivial', 'Minor', 'Major', 'Critical']);

            return parent::up();
        });

        $this->set_fim_fields(['name', 'description']);
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
