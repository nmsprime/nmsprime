<?php

use Illuminate\Database\Schema\Blueprint;

class UpdateOverdueDebtsAddColumns extends BaseMigration
{
    protected $tablename = 'overduedebts';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn(['fine1', 'fine2', 'fine3']);
            $table->float('dunning_charge1', 10, 4)->nullable();
            $table->float('dunning_charge2', 10, 4)->nullable();
            $table->float('dunning_charge3', 10, 4)->nullable();

            $table->text('dunning_text1')->nullable();
            $table->text('dunning_text2')->nullable();
            $table->text('dunning_text3')->nullable();

            $table->string('payment_period');

            // Block internet access when one of this numbers is exceeded if set
            $table->float('import_inet_block_amount')->nullable();
            $table->tinyInteger('import_inet_block_debts')->nullable();   // max number of debts
            $table->tinyInteger('import_inet_block_indicator')->nullable();
        });

        // Set default
        \DB::table($this->tablename)->where('id', 1)->update(['payment_period' => '14D']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn([
                'dunning_charge1',
                'dunning_charge2',
                'dunning_charge3',
                'dunning_text1',
                'dunning_text2',
                'dunning_text3',
                'payment_period',
                'import_inet_block_amount',
                'import_inet_block_debts',
                'import_inet_block_indicator',
            ]);

            $table->float('fine1', 10, 4);
            $table->float('fine2', 10, 4);
            $table->float('fine3', 10, 4);
        });
    }
}
