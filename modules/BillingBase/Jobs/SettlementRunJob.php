<?php

namespace Modules\BillingBase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;

class SettlementRunJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $sr;
    protected $sepaacc; 			// is set in constructor if we only wish to run command for specific SepaAccount

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SettlementRun $sr, SepaAccount $sa = null)
    {
        $this->sr = $sr;
        $this->sepaacc = $sa;
    }

    /**
     * Execute the job
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $this->sr->execute($this->sepaacc);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        \Log::error($exception);

        clearFailedJobs('\\SettlementRunJob');
    }
}
