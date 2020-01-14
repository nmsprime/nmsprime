<?php

namespace Modules\OverdueDebts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\OverdueDebts\Entities\Mt940Parser;

class ParseMt940 implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $path;
    protected $voucherNr;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($filepath, $voucherNr)
    {
        $this->path = $filepath;
        $this->voucherNr = $voucherNr;
    }

    /**
     * Execute the job
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $parser = new Mt940Parser();

        $parser->parse($this->path, $this->voucherNr);
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        \Log::error($exception);

        clearFailedJobs('\\Modules\OverdueDebts\Jobs\\ParseMt940');
    }
}
