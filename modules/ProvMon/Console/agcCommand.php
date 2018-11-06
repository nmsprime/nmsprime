<?php

namespace Modules\provmon\Console;

use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Cmts;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\ProvMon\Http\Controllers\ProvMonController;

class agcCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:agc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute Automatic Power Control based on measured SNR';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command. Automatically adjust power level of all CMTS
     *
     * @return true
     * @author: Ole Ernst
     */
    public function fire()
    {
        foreach (Cmts::all() as $cmts) {
            $ctrl = new ProvMonController();
            $ctrl->realtime_cmts($cmts, $cmts->get_ro_community(), true);
            unset($ctrl);
        }

        return true;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // array('example', InputArgument::REQUIRED, 'An example argument.'),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            // array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        ];
    }
}
