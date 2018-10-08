<?php

namespace Modules\Ccc\Console;

use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Contract;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateConnectionInformations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ccc:connInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a list of connection informations as a single concatenated PDF';

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
        $contracts = $this->select_contracts();
        $dir_path = storage_path('app/tmp/');
        $fn = 'connInfos.pdf';
        $controller = new \Modules\Ccc\Http\Controllers\CccUserController;

        if (! $contracts) {
            $msg = 'No Contracts selected to create connection informations!';

            if ($this->output) {
                echo "\n$msg";
            } else {
                Log::error($msg);
            }

            return 1;
        }

        // Create PDFs and concatenate them
        if ($this->output) {
            $bar = $this->output->createProgressBar(count($contracts));
        }

        foreach ($contracts as $c) {
            $files[] = $controller->connection_info_download($c->id, false);
            if ($this->output) {
                $bar->advance();
            }
        }

        $files_string = '';
        foreach ($files as $path) {
            $files_string .= "\"$path\" ";
        }

        \Log::debug('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile='.$dir_path.$fn.' <files>');
        system('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile='.$dir_path.$fn." $files_string", $ret);

        $this->info("\nConnection Info created: ".$dir_path.$fn);

        // Delete temp files
        foreach ($files as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        return $dir_path.$fn;
    }

    /**
     * Select Contracts by the defined options
     *
     * @return array
     */
    private function select_contracts()
    {
        $contracts1 = $contracts2 = $ids = $values = [];

        // get list of contracts
        if ($this->option('file')) {
            $content = file_get_contents($this->option('file'));
            $content = str_replace(["\n\r", "\n", "\r"], ',', $content);
            $values = explode(',', $content);
        }

        if ($this->option('list')) {
            $values = explode(',', $this->option('list'));
        }

        foreach ($values as $v) {
            if ($v) {
                $ids[] = trim($v);
            }
        }

        $contracts1 = Contract::whereIn('id', $ids, 'or')->orderBy('zip')->orderBy('city')->orderBy('street')->orderBy('house_number')->get()->all();

        if ($this->option('after')) {
            $contracts2 = Contract::where('created_at', '>', $this->option('after'))->get()->all();
        }

        return array_merge($contracts1, $contracts2);
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
            ['after', null, InputOption::VALUE_OPTIONAL, 'Date String - all Contracts that where add after a specific Date, e.g. 2017-07-21', null],
            ['file', null, InputOption::VALUE_OPTIONAL, 'File with Contract IDs - separated by newline and/or comma', []],
            ['list', null, InputOption::VALUE_OPTIONAL, 'Comma separated list of Contract IDs, e.g. 500034, 512456,634612', []],
        ];
    }
}
