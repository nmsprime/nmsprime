<?php

namespace Modules\BillingBase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BillingBase\Entities\SettlementRun;

class DeleteSettlementRun implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $settlementrun;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SettlementRun $settlementrun = null)
    {
        $this->settlementrun = $settlementrun;
    }

    public function handle()
    {
        $this->settlementrun->directory_cleanup();
        $this->settlementrun->delete();
    }

    public function failed(\ErrorException $exception)
    {
        \Log::error($exception);

        clearFailedJobs('\\DeleteSettlementRun');
    }
}
