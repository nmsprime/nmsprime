<?php

use Modules\ProvBase\Entities\Qos;
use Modules\ProvBase\Entities\QosObserver;

class InstallInitRadius extends BaseMigration
{
    protected $tablename = '';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $observer = new QosObserver;
        foreach (Qos::all() as $qos) {
            $observer->created($qos);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $observer = new QosObserver;
        foreach (Qos::all() as $qos) {
            $observer->deleted($qos);
        }
    }
}
