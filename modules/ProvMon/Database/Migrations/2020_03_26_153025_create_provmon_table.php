<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvmonTable extends Migration
{
    protected $tablename = 'provmon';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('start_frequency')->nullable();
            $table->integer('stop_frequency')->nullable();
            $table->integer('span')->nullable();
        });

        DB::update("INSERT INTO $this->tablename (start_frequency, stop_frequency, span) VALUES (154, 866, 8);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tablename);
    }
}
