<?php

namespace Modules\VoipMon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MatchRecordsCommand extends Command
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
    protected $name = 'voipmon:match_records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match voipmonitor call monitoring records to phonenumbers';

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
    public function handle()
    {
        // Name of the database accessed through $this->connection
        $database = \Schema::connection($this->connection)->getConnection()->getConfig('database');
        // Name of the database to match against
        $match_db = \Schema::getConnection()->getConfig('database');

        /*
         * If call originated from our network (i.e. *caller* matches) and
         * has not been processed yet (i.e. created_at is NULL) take *a* MOS value
         * If MOS value is not valid set it to 45 (best)
         */
        \DB::table($database.'.'.$this->tablename.' as c')->join($match_db.'.phonenumber as p', 'c.caller', 'like', \DB::raw('concat("%", p.prefix_number, p.number, "%")'))->whereNull('c.created_at')->update(['c.phonenumber_id' => \DB::raw('p.id'), 'c.mos_min_mult10' => \DB::raw('IF(c.a_mos_f1_min_mult10, c.a_mos_f1_min_mult10, 45)')]);
        /*
         * If call originated from external network (i.e. *called* matches) and
         * has not been processed yet (i.e. created_at is NULL) take *b* MOS value
         * If MOS value is not valid set it to 45 (best)
         */
        \DB::table($database.'.'.$this->tablename.' as c')->join($match_db.'.phonenumber as p', 'c.called', 'like', \DB::raw('concat("%", p.prefix_number, p.number, "%")'))->whereNull('c.created_at')->update(['c.phonenumber_id' => \DB::raw('p.id'), 'c.mos_min_mult10' => \DB::raw('IF(c.b_mos_f1_min_mult10, c.b_mos_f1_min_mult10, 45)')]);

        // If no match was found (i.e. phonenumber_id is NULL), use worst MOS of both directions
        \DB::connection($this->connection)->table($this->tablename)->whereNull('created_at')->whereNull('phonenumber_id')->update(['mos_min_mult10' => \DB::raw('LEAST(IF(a_mos_f1_min_mult10, a_mos_f1_min_mult10, 45), IF(b_mos_f1_min_mult10, b_mos_f1_min_mult10, 45))')]);

        // Set {created,updated}_at to callend to signify that matching was done
        \DB::connection($this->connection)->table($this->tablename)->whereNull('created_at')->update(['created_at' => \DB::raw('callend'), 'updated_at' => \DB::raw('callend')]);
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
