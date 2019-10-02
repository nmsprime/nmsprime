<?php

use Illuminate\Database\Schema\Blueprint;

class CreateContactTable extends BaseMigration
{
    protected $tablename = 'contact';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('firstname1');
            $table->string('lastname1');
            $table->string('firstname2')->nullable();
            $table->string('lastname2')->nullable();
            $table->string('company')->nullable();

            $table->string('tel')->nullable();
            $table->string('tel_private')->nullable();
            $table->string('email1')->nullable();
            $table->string('email2')->nullable();

            $table->string('street')->nullable();
            $table->string('house_nr')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('district')->nullable();

            $table->boolean('administration'); // Hausverwaltung

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
