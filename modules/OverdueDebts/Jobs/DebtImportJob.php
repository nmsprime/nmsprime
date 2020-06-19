<?php

namespace Modules\OverdueDebts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\OverdueDebts\Entities\DebtImport;

class DebtImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $runAsTest;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($path, $runAsTest = false)
    {
        $this->path = $path;
        $this->runAsTest = $runAsTest;
    }

    /**
     * Execute the job
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $debtImport = new DebtImport($this->path, $this->runAsTest);

        $debtImport->run();

        unlink($this->path);
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

        clearFailedJobs('\\Modules\OverdueDebts\Jobs\\ImportDebtJob');
    }
}
