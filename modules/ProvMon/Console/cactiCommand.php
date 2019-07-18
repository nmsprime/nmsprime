<?php

namespace Modules\provmon\Console;

use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Cmts;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\ProvBase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\ProvMon\Http\Controllers\ProvMonController;

class cactiCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:cacti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all missing Cablemodem Diagrams';

    protected $connection = 'mysql-cacti';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * DEBUG: use for monitoring km3 Marienberg Modems
     *        uncomment line in fire() for usage
     * TODO: delete me :)
     */
    private function _debug_ip()
    {
        return '10.42.2.'.rand(2, 253);
    }

    /**
     * Execute the console command. Create all missing Cacti Diagrams
     *
     * TODO: delete of unused diagrams
     *
     * @return true
     * @author: Torsten Schmidt
     */
    public function handle()
    {
        $matches = [];
        $path = '/usr/share/cacti/cli';

        try {
            if (! \Schema::connection($this->connection)->hasTable('host')) {
                return false;
            }
        } catch (\PDOException $e) {
            // Code 1049 == Unknown database '%s' -> cacti is not installed yet
            if ($e->getCode() == 1049) {
                return false;
            }
            // Don't catch other PDOExceptions
            throw $e;
        }

        $modems = $this->option('modem-id') === false ? Modem::all() : Modem::where('id', '=', $this->option('modem-id'))->get();
        foreach ($modems as $modem) {
            // Skip all $modem's that already have cacti graphs
            if (ProvMonController::monitoring_get_graph_ids($modem)->isNotEmpty()) {
                continue;
            }

            // Prepare VARs
            $name = $modem->hostname;
            $hostname = $modem->hostname.'.'.ProvBase::first()->domain_name;
            // DEBUG: use for monitoring km3 Marienberg Modems
            // $hostname  = $this->_debug_ip();
            $community = ProvBase::first()->ro_community;

            // Assumption: host template and graph tree are named 'cablemodem' (case-insensitive)
            $host_template_id = \DB::connection($this->connection)->table('host_template')
                ->where('name', '=', 'cablemodem')
                ->select('id')->first()->id;

            $graph_template_ids = \DB::connection($this->connection)->table('host_template_graph')
                ->join('host_template', 'host_template_graph.host_template_id', '=', 'host_template.id')
                ->where('host_template.name', '=', 'cablemodem')
                ->pluck('host_template_graph.graph_template_id');

            $tree_id = \DB::connection($this->connection)->table('graph_tree')
                ->where('name', '=', 'cablemodem')
                ->select('id')->first()->id;

            exec("php -q $path/add_device.php --description=$name --ip=$hostname --template=$host_template_id --community=$community --avail=snmp --version=2", $out);
            preg_match('/^Success - new device-id: \(([0-9]+)\)$/', end($out), $matches);
            if (count($matches) != 2) {
                continue;
            }

            // add host to cabelmodem tree
            // exec("php -q $path/add_tree.php --type=node --node-type=host --tree-id=$tree_id --host-id=$matches[1]");

            // create all graphs belonging to host template cablemodem
            foreach ($graph_template_ids as $id) {
                exec("php -q $path/add_graphs.php --host-id=$matches[1] --graph-type=cg --graph-template-id=$id");
            }

            // get first RRD belonging to newly created host
            $first = \DB::connection($this->connection)->table('host AS h')
                ->join('data_local AS l', 'h.id', '=', 'l.host_id')
                ->join('data_template_data AS t', 'l.id', '=', 't.local_data_id')
                ->where('h.id', '=', $matches[1])
                ->orderBy('t.id')
                ->select('t.id', 't.data_source_path')
                ->first();

            $stmnt = \DB::connection($this->connection)->table('host AS h')
                ->join('data_local AS l', 'h.id', '=', 'l.host_id')
                ->join('data_template_data AS t', 'l.id', '=', 't.local_data_id')
                ->where('h.id', '=', $matches[1]);

            // if possible choose preexisting rrd file, instead of creating a new one
            $file = glob('/var/lib/cacti/rra/'.$name.'_*.rrd');
            $data_source_path = $file ? str_replace('/var/lib/cacti/rra', '<path_rra>', $file[0]) : $first->data_source_path;
            $stmnt->update(['t.data_source_path' => $data_source_path]);

            // disable updating all RRDs except for first
            $stmnt->where('t.id', '!=', $first->id)->update(['t.active' => '']);

            // rebuild poller cache, since we changed the database manually
            exec("php -q $path/rebuild_poller_cache.php --host-id=$matches[1]");

            // Info Message
            //echo "\ncacti: create diagrams for Modem: $name";
            \Log::info("cacti: create diagrams for Modem: $name");
        }

        $cmtss = $this->option('cmts-id') === false ? Cmts::all() : Cmts::where('id', '=', $this->option('cmts-id'))->get();
        foreach ($cmtss as $cmts) {
            // Skip all $cmts's that already have cacti graphs
            if (ProvMonController::monitoring_get_graph_ids($cmts)->isNotEmpty()) {
                continue;
            }

            $name = $cmts->hostname;
            $hostname = $cmts->ip;
            $community = $cmts->get_ro_community();

            // Assumption: host template and graph tree are named e.g. '$company cmts' (case-insensitive)
            $host_template = \DB::connection($this->connection)->table('host_template')
                ->where('name', '=', $cmts->company.' cmts')
                ->select('id')->first();
            // we don't have a template for the company, skip adding the cmts
            if (! $host_template) {
                continue;
            }

            $tree_id = \DB::connection($this->connection)->table('graph_tree')
                ->where('name', '=', 'cmts')
                ->select('id')->first()->id;
            $query = \DB::connection($this->connection)->table('snmp_query')
                ->where('name', '=', 'SNMP - Interface Statistics')
                ->select('id')->first();
            // query doesn't exist yet, this can only happen during installation of cacti
            if (! $query) {
                continue;
            }

            $out = [];
            exec("php -q $path/add_device.php --description=\"$name\" --ip=$hostname --template=$host_template->id --community=\"$community\" --avail=snmp --version=2", $out);
            preg_match('/^Success - new device-id: \(([0-9]+)\)$/', end($out), $matches);
            if (count($matches) != 2) {
                continue;
            }

            // add "SNMP - Interface Statistics" query
            exec("php -q $path/add_data_query.php --host-id=$matches[1] --data-query-id=$query->id --reindex-method=1");
            // add host to cmts tree
            exec("php -q $path/add_tree.php --type=node --node-type=host --tree-id=$tree_id --host-id=$matches[1]");
        }

        echo "\n";

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
            ['cmts-id', null, InputOption::VALUE_OPTIONAL, 'only consider modem identified by its id, otherwise all', false],
            ['modem-id', null, InputOption::VALUE_OPTIONAL, 'only consider cmts identified by its id, otherwise all', false],
        ];
    }
}
