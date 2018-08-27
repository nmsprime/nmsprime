<?php

namespace Modules\VoipMon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DeleteOldRecordsCommand extends Command
{
    // Default config of the voipmonitor daemon is to create its own database, use it instead of the default db
    protected $connection = 'mysql-voipmonitor';
    // Name of the table
    protected $tablename = 'cdr';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'voipmon:delete_old_records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes voipmonitor call monitoring records older than 14 days';

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
     * Execute the console command.
     *
     * @author: Ole Ernst
     */
    public function fire()
    {
        // Delete records older than 14 days
        \DB::connection($this->connection)->table($this->tablename)->where('calldate', '<', \DB::raw('DATE_SUB(NOW(), INTERVAL 14 DAY)'))->delete();
        // Delete records with an ideal MOS score of 45
        //\DB::connection($this->connection)->table($this->tablename)->whereNotNull('created_at')->where('mos_min_mult10', '=', 45)->delete();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //['example', InputArgument::REQUIRED, 'An example argument.'],
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
            //['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
