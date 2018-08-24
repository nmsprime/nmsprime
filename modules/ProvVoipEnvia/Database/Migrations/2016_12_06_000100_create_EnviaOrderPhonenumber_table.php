<?php

use Illuminate\Database\Schema\Blueprint;

/**
 * As it turned out there is an n:m relation between EnviaOrders and Phonenumbers (instead of the
 * expected 1:n) – so we need this extra database table instead of the currently used foreign keys…
 *
 * @author Patrick Reichel
 */
class CreateEnviaOrderPhonenumberTable extends BaseMigration
{
    // name of the table to create
    protected $tablename = 'enviaorder_phonenumber';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->integer('enviaorder_id')->unsigned();
            $table->integer('phonenumber_id')->unsigned();
        });

        // if there are currently existing relations between enviaorders and phonenumbers:
        // write those to the freshly created table
        foreach (\DB::table('enviaorder')->whereNotNull('phonenumber_id')->orderBy('id')->get() as $enviaorder) {
            $created_at = $enviaorder->created_at;
            $enviaorder_id = $enviaorder->id;
            $phonenumber_id = $enviaorder->phonenumber_id;
            DB::update('INSERT INTO '.$this->tablename." (created_at, enviaorder_id, phonenumber_id) VALUES ('".$created_at."', ".$enviaorder_id.', '.$phonenumber_id.');');
        }

        return parent::up();
    }

    /**
     * Reverse the migrations.
     * As the relations between orders and phonenumbers cannot be inverted we don't rewrite the data here…
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tablename);
    }
}
