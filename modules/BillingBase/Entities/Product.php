<?php

namespace Modules\BillingBase\Entities;

use DB;

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
    public static $maturity_min = '1M';
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
            'maturity_min' => 'nullable|regex:/^\d+[dDmMyY]$/',
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

    public function view_belongs_to()
    {
        return $this->costcenter_id ? $this->costcenter : null;
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
            'index_header' => [$this->table.'.type', $this->table.'.name',  $this->table.'.price',
                'costcenter.name', $this->table.'.proportional', 'deprecated', ],
            'header' =>  $this->type.' - '.$this->name.' | '.$this->price.' €',
            'bsclass' => $bsclass,
            'eager_loading' => ['costcenter'],
            'edit' => ['costcenter.name' => 'getCostcenterName'],
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

    public function getCostcenterName()
    {
        return $costcenter = $this->costcenter ? $this->costcenter->name : '';
    }

    /**
     * Relationships:
     */
    public function quality()
    {
        return $this->belongsTo(\Modules\ProvBase\Entities\Qos::class, 'qos_id');
    }

    public function item()
    {
        return $this->hasMany(Item::class);
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'costcenter_id');
    }

    /**
     * Check if product can be deleted
     *
     * @author Roy Schneider, Patrick Reichel
     *
     * @return bool
     */
    public function delete()
    {
        // only delete if there are no items assigned for this product
        if ($this->item()->count() > 0) {
            $msg = trans('messages.assigned_items');
            $this->addAboveMessage($msg, 'error');

            return false;
        }

        return parent::delete();
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
                    // $prod_ids = self::where('type', '=', 'Internet')->where('qos_id', '!=', '0')->select('id')->get()->pluck('id')->all();
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
