<?php

namespace Modules\BillingBase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\SettlementRunZipper;

class ZipSettlementRun implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $settlementrun;

    protected $sepaacc;

    protected $postalInvoices;

    public function __construct(SettlementRun $settlementrun, SepaAccount $sepaacc = null, $postalInvoices = false)
    {
        $this->settlementrun = $settlementrun;
        $this->sepaacc = $sepaacc;
        $this->postalInvoices = $postalInvoices;
    }

    public function handle()
    {
        $zipper = new SettlementRunZipper($this->settlementrun);

        $zipper->fire($this->sepaacc, $this->postalInvoices);
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

        clearFailedJobs('\\SettlementRunZipperJob');
    }
}
