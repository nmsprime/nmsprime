<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateContactAddInvoiceText extends BaseMigration
{
    protected $tablename = 'contact';

    /**
     * Run the migrations. Add relation of realties to contract for a group contract
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->text('invoice_text1')->nullable();
            $table->text('invoice_text2')->nullable();
            $table->text('invoice_text3')->nullable();
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
            $table->dropColumn(['invoice_text1', 'invoice_text2', 'invoice_text3']);
        });
    }
}
