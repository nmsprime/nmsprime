<?php

namespace Modules\provbase\Console;

use Illuminate\Console\Command;
use Modules\BillingBase\Entities\Item;
use Modules\ProvBase\Entities\Contract;
// use Log;

use Modules\BillingBase\Entities\SepaMandate;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class importTvCustomersCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:importTV';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import Customers from CSV and add TV Tarif';

    public $important_todos = '';

    /**
     * Column Number and Description for easy Adaption
     */
    const C_NR = 0;
    const C_NAME = 1;
    const C_STRASSE = 2;
    const C_ZIP = 3;
    const C_CITY = 4;
    const C_TEL = 5;
    const C_FAX = 6;
    const C_MAIL = 7;
    const C_SALUT = 13;		// Anrede (Bemerkung)
    const C_DESC2 = 14; 		// Zusatz
    const C_DESC1 = 15;		// Watt
    const C_DESC3 = 16;		// Sonstiges
    const C_START = 20; 		// Eintritt
    const C_END = 21;		// Austritt

    // Sepa Data
    const S_REF = 0;
    const S_HOLDER = 8;
    const S_INST = 9;
    const S_BIC = 10;
    const S_IBAN = 11;
    const S_VALID = 12; 		// Zahlungsziel (invalid when = "14 Tage")
    const S_SIGNATURE = 24;

    // Item Data
    const TARIFF = 17; 		// Umlage
    const CREDIT = 19;		// Verstärkergeld

    /*
     * Global Variables - need adaption for every import
     * TODO: Change product IDs according to Database and yearly Charges according to CostCenter
     */
    const TV_CHARGE1 = 60; 		// Umlage in Euro
    const TV_CHARGE2 = 99999; 	// Umlage in Euro - Set to 99999 to disable second charge/tv-tariff
    const PRODUCT_ID1 = 66; 		// TV Billig
    const PRODUCT_ID2 = 0;		// TV Teuer

    // mapping of Watt amount to credit
    // Watt amount => product_id
    const CREDITS_WATT = [
        '4,5' 	=> 51,
        5 		=> 64,
        7 		=> 62, 	// & 63
        8 		=> 53,
        '8,5' 	=> 55,
        11 		=> 65,
        14 		=> 54,
        15 		=> 61,
        16 		=> 58,
        '16,5'  => 52, // & 57
        ];

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
     * Execute the console command - Import new Contracts with TV Tariff from a CSV-File (Separator: ";")
     *
     * See order and description of Columns ahead defined by constants
     *
     * @return mixed
     */
    public function fire()
    {
        if (! $this->confirm("IMPORTANT!!!\n\nHave global variables been adapted for this import?:
			(1) TV Charge in Euro?
			(2) TV product ID?
			")) {
            return;
        }

        $file_arr = file($this->argument('file'));
        $num = count($file_arr);
        $bar = $this->output->createProgressBar($num);

        // skip headline
        // unset($file_arr[0]);

        echo "Import TV customers\n";
        \Log::info("Import potentially $num TV customers");
        $bar->start();

        foreach ($file_arr as $line) {
            $bar->advance();

            $line = str_getcsv($line, ';');
            // $line = str_getcsv($line, "\t");
            $c = $this->_add_contract($line);

            if (! $c) {
                continue;
            }

            $this->_add_tarif($c, $line);
            $this->_add_Credit($c, $line);
            $this->_add_sepa_mandate($c, $line);
        }

        if ($this->important_todos) {
            echo $this->important_todos."\n";
        }
    }

    /**
     * Create new Contract or return existing one
     *
     * @return object  New created contract or if found the already existing one
     */
    private function _add_contract($line)
    {
        $number = $line[self::C_NR];
        $name = explode(',', $line[self::C_NAME]);
        $firstname = isset($name[1]) ? trim($name[1]) : trim($name[0]);
        $lastname = isset($name[1]) ? trim($name[0]) : '';
        $ret = importCommand::split_street_housenr($line[self::C_STRASSE]);
        $street = $ret[0];
        $housenr = $ret[1];
        $arr = explode(' OT ', $line[self::C_CITY]);
        $city = $arr[0];
        $district = isset($arr[1]) ? $arr[1] : '';

        $contract = Contract::where('number', '=', $number)->first();

        if ($contract) {
            // Check if name and address differs - could be a different customer
            if ($contract->firstname != $firstname || $contract->lastname != $lastname || $contract->street != $street) {
                // $msg = "Contract [$number] already exists with different name or street - Pls check & add manually!";
                $msg = "Vertragsnummer $number existiert bereits, aber Name, Straße oder Stadt weichen ab - Bitte fügen Sie den Vertrag manuell hinzu!";
                \Log::warning($msg);
                $this->important_todos .= "\n$msg";

                return null;
            }

            \Log::notice("Contract $number $firstname $lastname already exists - only add TV Tarif");

            return $contract;
        } else {
            // TODO: Check if customer/name & address already exists with another contract number
            $existing = Contract::where('firstname', '=', $firstname)->where('lastname', '=', $lastname)
                // make Straße or Str. respective ..straße or ..str. indifferent on searching in DB
                ->whereIn('street', [$street, str_replace(['traße', 'traße'], 'tr.', $street)])
                ->where('city', '=', $city)->first();

            if ($existing) {
                // $msg = "Customer $number is probably already added with different contract number [$existing->number] (found same name [$firstname $lastname], city & street [$street]). Check this manually!";
                $msg = "Kunde $number existiert bereits unter der Vertragsnummer $existing->number (selber Name, Stadt, Straße: $firstname $lastname, $city, $street gefunden). Füge nur Tarif hinzu.";
                \Log::warning($msg);

                return $existing;
            }
        }

        $contract = new Contract;

        $contract->contract_start = $line[self::C_START] ? date('Y-m-d', strtotime($line[self::C_START])) : '2000-01-01';
        $contract->contract_end = $line[self::C_END] ? date('Y-m-d', strtotime($line[self::C_END])) : null;

        // Discard contracts that ended last year
        if ($contract->contract_end && ($contract->contract_end < date('Y-01-01'))) {
            \Log::info("Contract $number is out of date ($contract->contract_start - $contract->contract_end)");

            return null;
        }

        $contract->number = $number;
        $contract->firstname = $firstname;
        $contract->lastname = $lastname;
        $contract->street = $street;
        $contract->house_number = $housenr;
        $contract->zip = str_pad($line[self::C_ZIP], 5, '0', STR_PAD_LEFT);
        $contract->city = $city;
        $contract->district = $district;

        // $contract->academic_degree = self::map_academic_degree($line[self::C_ACAD_DGR]);
        $contract->salutation = self::map_salutation($line[self::C_SALUT]);
        $contract->phone = str_replace(['/', '-', ' '], '', $line[self::C_TEL]);
        $contract->description = $line[self::C_DESC1]."\n".$line[self::C_DESC2]."\n".$line[self::C_DESC3];
        $contract->costcenter_id = $this->option('cc'); 		// Dittersdorf=1
        if ($this->option('ag')) {
            $contract->contact = $this->option('ag');
        }
        $contract->create_invoice = true;

        $contract->fax = $line[self::C_FAX];
        $contract->email = $line[self::C_MAIL];
        // $contract->birthday 	= $contract->geburtsdatum;

        // Set null-fields to '' to fix SQL import problem with null fields
        $relations = $contract->relationsToArray();
        $nullable = ['contract_end'];
        foreach ($contract->toArray() as $key => $value) {
            if (array_key_exists($key, $relations) || in_array($key, $nullable)) {
                continue;
            }

            if ($contract->{$key} == null) {
                $contract->{$key} = '';
            }
        }

        $contract->deleted_at = null;
        // Update or Create Entry
        $contract->save();

        // Log
        \Log::info("Add Contract $contract->number: $contract->firstname, $contract->lastname");

        return $contract;
    }

    public static function map_academic_degree($string)
    {
        if (strpos($string, 'Prof') !== false) {
            return 'Prof. Dr.';
        }

        if (strpos($string, 'Dr.') !== false) {
            return 'Dr.';
        }

        return '';
    }

    public static function map_salutation($string)
    {
        if (strpos($string, 'Damen und Herren') !== false) {
            return 'Firma';
        }

        if (strpos($string, 'Herr') !== false) {
            return 'Herr';
        }

        return 'Frau';
    }

    private function _add_tarif($contract, $line)
    {
        $tariff = $line[self::TARIFF];

        if (! $tariff) {
            \Log::debug("'Umlage' is zero or empty - don't add tariff");

            return;
        }

        $existing = false;
        if ($contract->items()->count()) {
            $existing = $contract->items->contains(function ($item, $value) {
                return in_array($item->product_id, [self::PRODUCT_ID1, self::PRODUCT_ID2]);
            });
        }

        if ($existing) {
            \Log::debug("Contract $contract->number already has TV Tariff assigned");

            return;
        }

        $amount = str_replace('EUR', '', $tariff);
        $amount = str_replace(',', '.', $tariff);
        $amount = (float) trim($amount);

        switch ($amount) {
            case  0: return;
            case self::TV_CHARGE2: $product_id = self::PRODUCT_ID2; break;
            case self::TV_CHARGE1: $product_id = self::PRODUCT_ID1; break;
            default:
                $msg = "Contract $contract->number is charged with $amount EUR. Please add Tariff manually!";
                $this->important_todos .= "\n$msg";
                \Log::warning($msg);

                return;
        }

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $product_id,
            'valid_from' 		=> $line[self::C_START] ?: '2000-01-01',
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
            ]);

        \Log::info("Add TV Tariff $product_id for Contract $contract->number");
    }

    private function _add_Credit($contract, $line)
    {
        $credit = $line[self::CREDIT];
        $watt_amount = $line[self::C_DESC1];

        if (! $credit) {
            return;
        }

        $product_id = 0;
        foreach (self::CREDITS_WATT as $watt => $prod_id) {
            if ($watt_amount == $watt) {
                if (in_array($watt, [7, '16,5'])) {
                    $this->important_todos .= "\nPlease check if contract $contract->number has correct credit assigned! (multiple possible)";
                }
                $product_id = $prod_id;
                break;
            }
        }

        if (! $product_id) {
            $this->important_todos .= "\nContract $contract->number [Old Contract Nr ".$line[self::C_NR]."] has credit of $credit € [Watt: $watt_amount]. Please add credit manually!";

            return;
        }

        $existing = false;
        if ($contract->items) {
            $existing = $contract->items->contains('product_id', $product_id);
        }

        if ($existing) {
            \Log::debug("Contract $contract->number already has Credit ".$product_id.' assigned');

            return;
        }

        // $credit_amount = str_replace('EUR', '', $credit);
        // $credit_amount = str_replace(',', '.', $credit);
        // $credit_amount = trim($credit_amount);

        if (date('Y') == date('Y', strtotime($contract->contract_start)) || date('Y') == date('Y', strtotime($contract->contract_end))) {
            $this->important_todos .= "\nPlease check Amplifier credit for Contract $contract->number as it's calculated partly for the year";
        }

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $product_id,
            'valid_from' 		=> $contract->contract_start,
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
            // 'credit_amount' 	=> $credit_amount,
            'costcenter_id' 	=> $this->option('cc'),
            ]);

        \Log::info("Add Credit [Product ID $product_id] for Amplifier to Contract $contract->number");
    }

    private function _add_sepa_mandate($contract, $line)
    {
        $valid = trim($line[self::S_VALID]) == 'einzug';

        if (! $valid) {
            \Log::debug("Contract $contract->number has no valid SepaMandate");

            // Set CostCenter for current SepaMandate if customer pays TV charge in cash
            SepaMandate::where('contract_id', '=', $contract->id)
                ->where(function ($query) {
                    $query->whereNull('costcenter_id')->orWhere('costcenter_id', '=', 0);
                })
                ->update(['costcenter_id' => $contract->costcenter_id]);

            return;
        }

        $signature_date = date('Y-m-d', strtotime($line[self::S_SIGNATURE]));

        if ($contract->sepamandates && $contract->sepamandates->contains('signature_date', $signature_date)) {
            \Log::notice("Contract $contract->number already has SEPA-mandate with signature date $signature_date");

            return;
        }

        SepaMandate::create([
            'contract_id' 		=> $contract->id,
            'reference' 		=> $line[self::C_NR],
            'signature_date' 	=> $signature_date,
            'sepa_holder' 		=> $line[self::S_HOLDER],
            'sepa_iban'			=> $line[self::S_IBAN],
            'sepa_bic' 			=> $line[self::S_BIC],
            'sepa_institute' 	=> $line[self::S_INST],
            'sepa_valid_from' 	=> date('Y-m-d', strtotime($line[self::S_SIGNATURE])),
            'state' 			=> 'RCUR',
            'costcenter_id' 	=> $this->option('cc'),
            // 'sepa_valid_to' 	=> NULL,
            ]);

        \Log::info('Add SepaMandate [IBAN: '.$line[self::S_IBAN]."] for contract $contract->number");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED, 'Structured CSV FILE (-path) with Customer Data.'],
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
            ['cc', null, InputOption::VALUE_REQUIRED, 'CostCenter ID for all the imported Contracts', 0],
            ['ag', null, InputOption::VALUE_OPTIONAL, 'Antenna Community ID for all the imported Contracts', 0],
        ];
    }
}
