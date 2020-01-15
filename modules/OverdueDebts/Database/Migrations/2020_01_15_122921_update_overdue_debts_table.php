<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOverdueDebtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('overduedebts', function (Blueprint $table) {
            $table->decimal('fee', 10, 4)->nullable()->change();
            $table->string('payment_period')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('overduedebts', function (Blueprint $table) {
            $table->float('fee', 10, 4)->change();
            $table->string('payment_period')->change();
        });
    }
}
