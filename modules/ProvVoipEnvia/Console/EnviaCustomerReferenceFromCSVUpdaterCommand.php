<?php

namespace Modules\ProvVoipEnvia\Console;

use Log;
use Illuminate\Console\Command;
use Modules\ProvBase\Entities\Contract;

/**
 * Class for updating database with carrier codes from csv file
 */
class EnviaCustomerReferenceFromCSVUpdaterCommand extends Command
{
    // get some methods used by several updaters
    use \App\Console\Commands\DatabaseUpdaterTrait;

    /**
     * The console command name.
     */
    protected $name = 'provvoipenvia:update_envia_customer_references_from_csv';

    /**
     * The console command description.
     */
    protected $description = 'Updates envia TEL customer references from CSV file delivered by envia TEL {path_to_csv_file}';

    /**
     * The signature (defining the optional argument)
     */
    protected $signature = 'provvoipenvia:update_envia_customer_references_from_csv
							{csv_file : The file to be used to update envia TEL customer references}';

    /**
     * Execute the console command.
     *
     * @return null
     */
    public function handle()
    {
        Log::info($this->description);
        echo "\n";

        $csv_file = $this->argument('csv_file');
        if (! is_readable($csv_file)) {
            echo "ERROR: File $csv_file does not exist or is not readable";
            echo "\n";
            exit(1);
        }

        $csv = $this->_read_csv($csv_file);
        $this->_update_contracts($csv);

        echo "\n\n";
    }

    /**
     * Read the given CSV file in array.
     *
     * @author Patrick Reichel
     */
    protected function _read_csv($csv_file)
    {
        $delimiter = ',';
        $enclosure = '"';

        $fh = fopen($csv_file, 'r');
        $csv_head = fgetcsv($fh, 0, $delimiter, $enclosure);
        $csv = [];

        while (($row = fgetcsv($fh, 0, $delimiter, $enclosure)) !== false) {
            $num = count($row);

            $data = [];
            for ($i = 0; $i < $num; $i++) {
                $data[$csv_head[$i]] = trim($row[$i]);
            }
            array_push($csv, $data);
        }

        fclose($fh);

        return $csv;
    }

    /**
     * Updates our contract table using the imported CSV.
     *
     * @author Patrick Reichel
     */
    protected function _update_contracts($csv)
    {
        Log::info('Updating contracts (envia TEL customer reference) by data from envia TEL CSV');

        // older contracts have been created at envia TEL using contract numbers with several prefixes
        // our new NMS don't use this prefixes
        // can be an empty array if there are no prefixes
        $prefixes_to_be_removed = [
            '002-',
        ];

        // map the CSV fields to variables ⇒ later this can easily be changed
        $field_customer_nr = 'ACCOUNTNO';
        $field_new_envia_customer_reference = 'EXTERNAL_REFERENCE NEU';
        $field_firstname = 'FIRSTNAME';
        $field_lastname = 'LASTNAME';

        foreach ($csv as $key => $data) {

            // get the currently used contract number (removing the defined prefixes)
            $customer_nr = $data[$field_customer_nr];
            $customer_nr_clean = $customer_nr;
            foreach ($prefixes_to_be_removed as $prefix) {
                $customer_nr_clean = str_replace($prefix, '', $customer_nr_clean);
            }

            $combined_name_data = trim($data[$field_lastname].' '.$data[$field_firstname]);

            $contract = Contract::where('number', '=', $customer_nr_clean)->first();
            if (! $contract) {
                $msg = "No contract for $customer_nr_clean ($combined_name_data)";
                echo "\nERROR: $msg";
                Log::error($msg);
                continue;
            }

            $combined_name_db = $contract->lastname.' '.$contract->firstname;
            $combined_name_db_reversed = $contract->firstname.' '.$contract->lastname;
            if (
                ($combined_name_data != $combined_name_db)
                &&
                ($combined_name_data != $combined_name_db_reversed)
            ) {
                $msg = "Name mismatch for $customer_nr ($combined_name_db != $combined_name_data)";
                echo "\nERROR: $msg";
                Log::error($msg);
                continue;
            }

            $contract->number3 = $customer_nr_clean;
            $contract->number4 = $data[$field_customer_nr];
            $contract->customer_external_id = $data[$field_new_envia_customer_reference];
            Log::info("Changing contract $contract->id: customer external id is ".$data[$field_new_envia_customer_reference]);

            $contract->save();
        }
    }
}
