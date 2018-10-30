<?php

namespace Modules\BillingBase\Entities;

use DB;
use Str;
use Session;

class Product extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'product';

    /**
     * The default Period of Notice (14 days) and maturity for products where string is not set in DB
     * (needed for Invoice creation during SettlementRun)
     *
     * @var string 	pon
     * @var string  maturity
     */
    public static $pon = '14D';
    public static $maturity = '1M';

    // Add your validation rules here
    public static function rules($id = null)
    {
        // Pay attention to the prepare_rules()-function in Controller
        return [
            'name' 	=> 'required|unique:product,name,'.$id.',id,deleted_at,NULL',
            'type' 	=> 'required|not_null',
            // 'type' => "required|not_null|unique:product,type,$id,id,type,Credit,deleted_at,NULL",	// if credit shall exist only once
            'voip_sales_tariff_id' => 'required_if:type,Voip',
            'voip_purchase_tariff_id' => 'required_if:type,Voip',
            'qos_id' => 'required_if:type,Internet',
            'price'  => 'required_if:type,Internet,Voip,TV,Other,Device,Mixed',
            'maturity' => 'nullable|regex:/^\d+[dDmMyY]$/',
            'period_of_notice' => 'nullable|regex:/^\d+[dDmMyY]$/',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Product Entry';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-th-list"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
                'index_header' => [$this->table.'.type', $this->table.'.name',  $this->table.'.price'],
                'header' =>  $this->type.' - '.$this->name.' | '.$this->price.' €',
                'bsclass' => $bsclass,
                'order_by' => ['0' => 'asc'], ];  // columnindex => direction
    }

    public function get_bsclass()
    {
        switch ($this->type) {
            case 'Internet':	$bsclass = 'info'; break; // online
            case 'TV': $bsclass = 'warning'; break; // warning
            case 'Voip': $bsclass = 'success'; break; // critical
            case 'Device': $bsclass = 'warning'; $status = 'offline'; break; // offline
            case 'Credit': $bsclass = 'danger'; $status = 'offline'; break; // offline
            case 'Other': $bsclass = 'info'; $status = 'offline'; break; // offline

            default: $bsclass = 'danger'; break;
        }

        return $bsclass;
    }

    /**
     * Relationships:
     */
    public function quality()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Qos', 'qos_id');
    }

    public function item()
    {
        return $this->hasMany('Modules\BillingBase\Entities\Item');
    }

    public function costcenter()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\CostCenter', 'costcenter_id');
    }

    /**
     * Check if product can be deleted
     *
     * @author Roy Schneider
     *
     * @return bool
     */
    public function delete()
    {
        // only delete if there are no items assigned for this product
        if ($this->item()->count() > 0) {
            Session::push('tmp_error_above_index_list', trans('messages.assigned_items'));

            return false;
        }

        return $this->generateAboveInfo($this->_delete());
    }

    /**
     * Generate general above message when deleting a product
     *
     * @author Roy Schneider
     *
     * @return null
     *
     * @param bool     deleted
     */
    public function generateAboveInfo($deleted)
    {
        // check from where the deletion request has been triggered and set the correct var to show information
        $prev = explode('?', \URL::previous())[0];
        $prev = Str::lower($prev);

        $msg = trans('messages.Product_Successfully_Deleted', ['id' => $this->id]);

        if (Str::endsWith($prev, 'edit')) {
            Session::push('tmp_success_above_relations', $msg);
        }

        return Session::push('tmp_success_above_index_list', $msg);
    }

    /*
     * Other Functions
     */

    /**
     * Returns an array with all ids of a specific product type
     *
     * NOTE: DB::table is approximately 100x faster than Eloquent here and this function
     *	is called for every Contract during daily_conversion
     *
     * @param 	String/Enum 	[internet|voip|tv]
     * @return 	array
     *
     * @author Nino Ryschawy
     */
    public static function get_product_ids($type)
    {
        switch (strtolower($type)) {
            case 'internet':
                $prod_ids = DB::table('product')->where('type', '=', $type)
                    ->where('qos_id', '!=', '0')->where('deleted_at', '=', null)->select('id')->get();
                    // $prod_ids = Product::where('type', '=', 'Internet')->where('qos_id', '!=', '0')->select('id')->get()->pluck('id')->all();
                break;

            case 'voip':
                $prod_ids = DB::table('product')
                    ->where('deleted_at', '=', null)->where('type', '=', 'Voip')
                    ->where(function ($query) {
                        $query
                        ->where('voip_sales_tariff_id', '!=', '0')
                        ->orWhere('voip_purchase_tariff_id', '!=', '0');
                    })
                    ->select('id')->get();
                break;

            case 'tv':
                $prod_ids = DB::table('product')->where('type', '=', 'TV')->where('deleted_at', '=', null)->select('id')->get();
                break;

            default:
                return;
        }

        $ids = [];

        foreach ($prod_ids as $prod) {
            array_push($ids, $prod->id);
        }

        return $ids;
    }

    /**
     * Returns an array of available product types
     *
     * @return array|null
     * @throws \Exception
     */
    public static function get_product_types()
    {
        $ret_val = null;

        try {
            $products = DB::table('product')->groupBy('type')->get();
            foreach ($products as $product) {
                $ret_val[] = $product->type;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $ret_val;
    }
}
