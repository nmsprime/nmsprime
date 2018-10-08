<?php

namespace Modules\hfcbase\Console;

use Illuminate\Console\Command;
use Modules\HfcReq\Entities\NetElement;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TreeBuildCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:tree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HfcBase: Tree - build net and cluster index';

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
     * @return mixed
     */
    public function fire()
    {
        NetElement::relation_index_build_all(2);
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
            ['debug', 'd', InputOption::VALUE_OPTIONAL, 'Debug Net and Cluster Outputs', 0],
        ];
    }
}
