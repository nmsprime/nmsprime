<?php

namespace Modules\HfcCustomer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MpsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the console command - Refresh all MPS rules
     *
     * @return mixed
     */
    public function handle()
    {
        \Modules\HfcCustomer\Entities\Mpr::ruleMatching();
    }
}
