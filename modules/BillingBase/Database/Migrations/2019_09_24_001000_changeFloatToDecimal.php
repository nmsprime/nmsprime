<?php

use Illuminate\Database\Schema\Blueprint;

class ChangeFloatToDecimal extends BaseMigration
{
    /**
     * As float is inaccurate the best way to store money amounts accurate is via decimal type
     *
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item', function (Blueprint $table) {
            $table->decimal('credit_amount', 10, 4)->nullable()->change();
        });

        // Change enums to string because of Laravel DBAL bug: https://stackoverflow.com/questions/29165259/laravel-db-migration-renamecolumn-error-unknown-database-type-enum-requested/32860409#32860409
        DB::statement('ALTER TABLE product MODIFY COLUMN type varchar(50) NOT NULL');
        DB::statement('ALTER TABLE product MODIFY COLUMN billing_cycle varchar(50) NOT NULL');

        Schema::table('product', function (Blueprint $table) {
            $table->decimal('price', 10, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item', function (Blueprint $table) {
            $table->float('credit_amount')->nullable()->change();
        });

        DB::statement("ALTER TABLE product MODIFY COLUMN type ENUM('Internet','TV','Voip','Device','Credit','Other','Postal') NOT NULL");
        DB::statement("ALTER TABLE product MODIFY COLUMN type ENUM('Once','Monthly',Querterly','Yearly') NOT NULL");

        Schema::table('product', function (Blueprint $table) {
            $table->decimal('price', 10, 4)->nullable()->change();
        });
    }
}
