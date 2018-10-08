<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCccAuthuserTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'cccauthusers';

    // password for inital superuser
    protected $initial_superuser_password = 'toor';

    /**
     * Run the migrations.
     *
     * NOTE: this is a simple copy of Authuser Migration from Patrick Reichel. See base app migrations.
     *       This is/will/could be adapted to CCC requirements!
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('login_name', 191);
            $table->string('password', 60);
            $table->string('description');
            $table->boolean('active')->default(1);
            $table->integer('contract_id')->unsigned();
            $table->rememberToken();

            $table->unique('login_name');
        });

        // the following “seeding” is needed in every case – even if the seeders will not be run!
        // add superuser => needed to configure the monster
        //
        // TODO: this should be disabled in production systems!
        DB::table($this->tablename)->insert([
            'id' => 1,
            'first_name' => 'superuser',
            'last_name' => 'initial',
            'email' => 'root@localhost',
            'login_name' => 'root',
            'password' => Hash::make($this->initial_superuser_password),
            'description' => 'Superuser to do base config. Initial password is “'.$this->initial_superuser_password.'” – change this ASAP or delete this user!!',
            'contract_id' => 500000,
        ]);
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
