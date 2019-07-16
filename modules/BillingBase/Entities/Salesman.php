<?php

namespace Modules\BillingBase\Entities;

use Storage;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseViewController;

class Salesman extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'salesman';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'firstname' 	=> 'required',
            'lastname' 		=> 'required',
            'commission'	=> 'required|numeric|between:0,100',
            'products' 		=> 'product',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Salesman';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-vcard"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return ['table' => $this->table,
                'index_header' => [$this->table.'.id', $this->table.'.lastname', $this->table.'.firstname'],
                'order_by' => ['0' => 'asc'],  // columnindex => direction
                'header' =>  $this->lastname.' '.$this->firstname, ];
    }

    // View Relation.
    public function view_has_many()
    {
        $ret['Edit']['Contract']['class'] = 'Contract';
        $ret['Edit']['Contract']['relation'] = $this->contracts;

        return $ret;
    }

    /**
     * Relationships:
     */
    public function contracts()
    {
        return $this->hasMany('Modules\ProvBase\Entities\Contract');
    }

    /**
     * BILLING STUFF
     */

    // all items he gets commission for (in actual billing cycle)
    protected $items = [0 => [
        // salesman specific fields
        'salesman_id' => 0,
        'salesman.firstname' => '',
        'salesman.lastname' => '',
        'commission in %' => 0,
        // item sepecific
        'contract_nr' => 0,
        'contract_name' => '',
        'contract_start' => '',
        'contract_end' => '',
        'product_name' => '',
        'product_type' => '',
        'product_count' => 0,
        'charge' => 0,
        'salesman_commission' => 0,
        'sepaaccount_id' => 0,
    ]];

    public static $filename = 'salesmen_commission';

    /**
     * Add the necessary infos of the item to the list
     *
     * @param object contract
     * @param object item
     * @param int sepaaccount_id 	necessary on split of settlementrun
     */
    public function add_item($contract, $item, $sepaaccount_id)
    {
        if (stripos($this->products, $item->product->type) === false) {
            return;
        }

        // if (!isset($this->total_commission[$sepaaccount_id]))
        // 	$this->total_commission[$sepaaccount_id] = 0;
        // $this->total_commission[$sepaaccount_id] += $item->charge;

        $this->items[] = [
            'contract_nr' 		=> $contract->number,
            'contract_name' 	=> "$contract->lastname, $contract->firstname",
            'contract_start' 	=> $contract->contract_start,
            'contract_end' 		=> $contract->contract_end,
            'product_name' 		=> $item->product->name,
            'product_type' 		=> $item->product->type,
            'product_count' 	=> $item->count,
            'charge' 			=> $item->charge,
            'sepaaccount_id' 	=> $sepaaccount_id,
            ];
    }

    /**
     * Return filename of Salesman Commissions with path relativ to storage/app/
     */
    public static function get_storage_rel_filename()
    {
        return SettlementRun::get_relative_accounting_dir_path().'/'.BaseViewController::translate_label(self::$filename).'.txt';
    }

    /**
     * Write headline in salesman.csv
     */
    public function prepare_output_file()
    {
        $rel_path = self::get_storage_rel_filename();

        if (Storage::exists($rel_path)) {
            return;
        }

        $arr = [];
        foreach (array_keys($this->items[0]) as $col) {
            $arr[] = trans("dt_header.$col");
        }

        Storage::put($rel_path, implode(';', $arr));
    }

    /**
     * Fill salesman.csv with the data of this salesman
     */
    public function print_commission()
    {
        $infos = [
            'salesman_id' 		 => $this->id,
            'salesman.firstname' => $this->firstname,
            'salesman.lastname'  => $this->lastname,
            'commission in %' 	 => number_format_lang($this->commission),
            ];

        foreach ($this->items as $key => $array) {
            if ($key == 0) {
                continue;
            }

            $array = array_replace($this->items[0], $infos, $array);

            $charge = $array['charge'] * $this->commission / 100;
            $array['salesman_commission'] = number_format_lang($charge);
            $array['charge'] = number_format_lang($array['charge']);

            // replace csv separator
            foreach ($array as $key => $value) {
                $array[$key] = str_replace(';', ',', $value);
            }

            // Note: fputcsv($fh, $array, ';') doesnt enclose all fields
            Storage::append(self::get_storage_rel_filename(), implode(';', $array));
        }
    }

    /**
     * Remove SEPA account specific entries from csv on repetition of the settlementrun
     */
    public static function remove_account_specific_entries_from_csv($sepaaccount_id)
    {
        $rel_path = self::get_storage_rel_filename();
        $path = storage_path("app/$rel_path");

        if (! is_file($path)) {
            return;
        }

        $lines = file($path);

        foreach ($lines as $key => $line) {
            if ($key == 0) {
                continue;
            }

            if (! $line || $line == PHP_EOL) {
                unset($lines[$key]);
                continue;
            }

            $arr = str_getcsv($line, ';');

            if (intval($arr[13]) == $sepaaccount_id) {
                unset($lines[$key]);
            }
        }

        Storage::put($rel_path, implode($lines));
        // Storage::put($rel_path, implode($lines, "\n"));
    }
}
