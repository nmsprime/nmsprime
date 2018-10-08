<?php

use Illuminate\Database\Schema\Blueprint;

class CreateCommentTable extends BaseMigration
{
    protected $tablename = 'comment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('user_id')->nullable();
            $table->integer('ticket_id')->nullable();
            $table->text('comment');

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
