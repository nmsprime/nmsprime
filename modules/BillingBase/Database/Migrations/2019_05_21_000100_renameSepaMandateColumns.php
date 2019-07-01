<?php

use Illuminate\Database\Schema\Blueprint;

class RenameSepaMandateColumns extends BaseMigration
{
    protected $tablename = 'sepamandate';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            // $table->string('sepa_holder')->nullable()->change();
            // $table->string('sepa_iban', 34)->nullable()->change();
            // $table->string('sepa_bic', 11)->nullable()->change();
            // $table->string('sepa_institute')->nullable()->change();

            // $table->renameColumn('sepa_holder', 'holder');
            // $table->renameColumn('sepa_iban', 'iban');
            // $table->renameColumn('sepa_bic', 'bic');
            // $table->renameColumn('sepa_institute', 'institute');
            // $table->renameColumn('sepa_valid_from', 'valid_from');
            // $table->renameColumn('sepa_valid_to', 'valid_to');

            // use DB::statement because of Laravel Bug with enums
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_holder holder varchar(255) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_iban iban varchar(34)");
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_bic bic varchar(11) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_institute institute varchar(255) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_valid_from valid_from date");
            DB::statement("ALTER TABLE $this->tablename CHANGE sepa_valid_to valid_to date null");
            DB::statement("ALTER TABLE $this->tablename CHANGE disable disable tinyint(1) not null");
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
            // use DB::statement because of Laravel Bug with enums
            DB::statement("ALTER TABLE $this->tablename CHANGE holder sepa_holder varchar(255) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE iban sepa_iban varchar(34)");
            DB::statement("ALTER TABLE $this->tablename CHANGE bic sepa_bic varchar(11) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE institute sepa_institute varchar(255) null");
            DB::statement("ALTER TABLE $this->tablename CHANGE valid_from sepa_valid_from date");
            DB::statement("ALTER TABLE $this->tablename CHANGE valid_to sepa_valid_to date null");

            // $table->renameColumn('holder', 'sepa_holder');
            // $table->renameColumn('iban', 'sepa_iban');
            // $table->renameColumn('bic', 'sepa_bic');
            // $table->renameColumn('institute', 'sepa_institute');
            // $table->renameColumn('valid_from', 'sepa_valid_from');
            // $table->renameColumn('valid_to', 'sepa_valid_to');
        });
    }
}
