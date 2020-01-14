<?php

namespace Modules\BillingBase\Entities;

use DB;
use Log;

class NumberRange extends \BaseModel
{
    public $table = 'numberrange';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name'		=> 'required',
            'start'		=> 'required|numeric',
            'end'		=> 'required|numeric',
        ];
    }

    public static function view_headline()
    {
        return 'Numberranges';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-globe"></i>';
    }

    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [$this->table.'.id', $this->table.'.name', $this->table.'.prefix', $this->table.'.suffix', $this->table.'.start', $this->table.'.end', 'costcenter.name'],
            'header' => $this->id.' - '.$this->name,
            'order_by' => ['0' => 'asc'],
            'eager_loading' => ['costcenter'],
        ];
    }

    /**
     * Relationships
     */
    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'costcenter_id');
    }

    /**
     * Return translated NumberRange types (Contract|Invoice)
     */
    protected static function get_types()
    {
        $ret = [];
        $types = self::getPossibleEnumValues('type');

        foreach ($types as $key => $name) {
            $ret[$key] = \App\Http\Controllers\BaseViewController::translate_view($name, 'Numberrange_Type');
        }

        return $ret;
    }

    /**
     * @return string
     */
    public static function get_new_number($type, $costcenter_id)
    {
        $new_number = null;

        switch ($type) {

            case 'invoice':
                $new_number = self::get_new_invoice_number($costcenter_id);
                break;

            default:
                $new_number = self::get_next_contract_number($costcenter_id);
        }

        return $new_number;
    }

    /**
     * Get next available Contract number
     *
     * Note: Also uses free, not yet assigned numbers in between
     * See https://stackoverflow.com/questions/5016907/mysql-find-smallest-unique-id-available
     *
     * @author Nino Ryschawy
     *
     * @return string 	PrefixNumberSuffix
     */
    protected static function get_next_contract_number($costcenter_id)
    {
        // check if costcenter_id is given
        if (is_null($costcenter_id) || ($costcenter_id == '0') || ($costcenter_id == 0)) {
            Log::warning('No costcenter_id given – cannot get next contract number');

            return;
        }

        $numberranges = self::where('type', '=', 'contract')->where('costcenter_id', $costcenter_id)->orderBy('id')->get();

        if (! $numberranges) {
            // Log::info("No NumberRange assigned to CostCenter [$costcenter_id]!");
            return;
        }
        foreach ($numberranges as $range) {
            $first = \Modules\ProvBase\Entities\Contract::where('number', '=', $range->prefix.$range->start.$range->suffix)->get(['number'])->all();

            if (! $first) {
                return $range->prefix.$range->start.$range->suffix;
            }

            $length_min = strlen($range->prefix.$range->start.$range->suffix);

            // join table with itself and check if number+1 is already assigned - if not, it's free and returned
            $num = DB::table('contract as c1')
                ->select(DB::raw("min(substring(c1.number, char_length('$range->prefix') + 1,
					char_length(c1.number) - char_length('$range->prefix') - char_length('$range->suffix'))+1) as nextNum"))
                // increment number between pre- & suffix and check if it's assigned (if not: c2.number=null)
                ->leftJoin('contract as c2', DB::raw("CONCAT('$range->prefix',
					substring(c1.number, char_length('$range->prefix') + 1,
						char_length(c1.number) - char_length('$range->prefix') - char_length('$range->suffix'))+1,
					'$range->suffix')"), '=', 'c2.number')
                ->whereNull('c2.number')
                ->where('c1.costcenter_id', '=', $costcenter_id)
                // only consider numbers where prefix and suffix really exists
                ->where(DB::raw('char_length(c1.number)'), '>=', $length_min)
                ->where(DB::raw("substring(c1.number, 1, char_length('$range->prefix'))"), '=', $range->prefix ?: '')
                ->where(DB::raw("substring(c1.number, -char_length('$range->suffix'))"), '=', $range->suffix ?: '')
                // filter out all numbers not in predefined range
                ->whereBetween(DB::raw("substring(c1.number, char_length('$range->prefix') + 1,
							char_length(c1.number) - char_length('$range->prefix') - char_length('$range->suffix'))"),
                        [$range->start, $range->end])
                ->get();

            $num = $num[0]->nextNum;
            if (! $num) {
                Log::warning("Could not find a free number in number range $range->name", [$range->id]);

                $wherebetween = DB::raw("substring(number, char_length('$range->prefix') + 1,
							char_length(number) - char_length('$range->prefix') - char_length('$range->suffix'))");

                $last = \Modules\ProvBase\Entities\Contract::select('number')
                    ->whereBetween($wherebetween, [$range->start, $range->end])
                    ->where('costcenter_id', '=', $costcenter_id)
                    ->orderBy('number', 'desc')->first()->number;

                if ($last >= $range->end) {
                    continue;
                }

                // check if there are contracts with different costcenter_id inside the range when last number is smaller than the end of the range
                $contract_nrs = \Modules\ProvBase\Entities\Contract::where('costcenter_id', '!=', $costcenter_id)
                    ->whereBetween($wherebetween, [$range->start, $range->end])
                    ->select('number')
                    ->orderBy('number')->pluck('number')->all();

                if ($contract_nrs) {
                    // session(['alert.warning' => trans('messages.contract_nr_mismatch', ['nrs' => implode(', ', $contract_nrs)])]);
                    \Session::push('tmp_error_above_form', trans('messages.contract_nr_mismatch', ['nrs' => implode(', ', $contract_nrs)]));
                } else {
                    // Check for deleted contracts with number to costcenter_id mismatch as this could lead to null as return value of the query too
                    $contract_nrs_trashed = \Modules\ProvBase\Entities\Contract::where('costcenter_id', '!=', $costcenter_id)
                        ->withTrashed()
                        ->whereBetween($wherebetween, [$range->start, $range->end])
                        ->select('number')
                        ->orderBy('number')->pluck('number')->all();

                    if ($contract_nrs_trashed) {
                        Log::error('NumberRange - Get next contract number failes because there are deleted contracts ('.implode(', ', $contract_nrs_trashed).') with a wrong costcenter_id - their contract number must originally have belong to another costcenter and number range');
                    }
                }

                continue;
            } elseif ($num > $range->end) {
                Log::warning("No free contract number in number range: $range->name [$range->id]");
                continue;
            }

            return $range->prefix.$num.$range->suffix;
        }

        $cc = CostCenter::find($costcenter_id);
        Log::alert('No free contract numbers under all number ranges of cost center: '.$cc->name.' ['.$cc->id.']');
    }

    protected static function get_new_invoice_number()
    {
    }
}
