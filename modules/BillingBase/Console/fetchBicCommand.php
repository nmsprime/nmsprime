<?php

namespace Modules\BillingBase\Console;

use Storage;
use Illuminate\Console\Command;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class fetchBicCommand extends Command
{
    /**
     * The console command & table name
     *
     * @var string
     */
    protected $name = 'billing:bic';
    protected $description = 'Fetch BIC data from national bank and save to file';

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
     * Execute the console command
     *
     * TODO: create array of URLs (files with BICs) and store each file as CSV
     */
    public function handle()
    {
        $start = time();

        // Download data as xlsx-file from national bank
        $filename = 'https://www.bundesbank.de/Redaktion/DE/Downloads/Aufgaben/Unbarer_Zahlungsverkehr/Bankleitzahlen/2016_09_04/blz_2016_06_06_xls.xlsx?__blob=publicationFile';
        $data = file_get_contents($filename);
        Storage::put('tmp/data.xlsx', $data);

        $now = time();
        echo 'Downloaded file from url ['.($now - $start)."s]\nParse data:\n";

        // extract relevant data and convert to csv
        $file = storage_path().'/app/tmp/data.xlsx';
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($file);
        $data = [];
        $n = 1;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowObject) {
                $row = $rowObject->toArray();
                if (! $row[0]) {
                    break;
                }

                // only when bic is set - avoid adding redundant data
                if ($row[7] && ! array_key_exists($row[0], $data)) {
                    $data[$row[0]] = implode(';', [$row[0], $row[2], str_replace(',', '-', $row[4]), $row[7]]);
                }

                if ($n % 1000 == 0) {
                    $now = time();
                    echo 'row '.$n.' after: '.($now - $start)." s\n";
                }
                $n++;
            }
        }

        // Store data of german bics
        $file = 'config/billingbase/bic_de.csv';
        Storage::put($file, implode("\n", $data));
        Storage::delete('tmp/data.xlsx');
        echo 'Successfully created '.storage_path('app/').$file."\n";
        system('/bin/chown -R apache '.storage_path('app/config/billingbase'));
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
        ];
    }

    protected function getOptions()
    {
        return [
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
