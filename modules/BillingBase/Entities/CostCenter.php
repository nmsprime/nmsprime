<?php

namespace Modules\BillingBase\Entities;

class CostCenter extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'costcenter';

    // Add your validation rules here
    public static function rules($id = null)
    {
        // this is to avoid missing customer payments when changing the billing month during the year
        // $m = date('m');

        return [
            'name' 			=> 'required',
            'billing_month' => 'Numeric', //|Min:'.$m,
        ];
    }

    /**
     * Observers
     */
    public static function boot()
    {
        self::observe(new CostCenterObserver);
        parent::boot();
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Cost Center';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-creative-commons"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return ['table' => $this->table,
                'index_header' => [$this->table.'.name', $this->table.'.number', 'sepaaccount.name'],
                'header' =>  $this->name,
                'order_by' => ['0' => 'asc'],  // columnindex => direction
                'eager_loading' => ['sepaaccount'], ];
    }

    public function view_belongs_to()
    {
        return $this->sepaaccount;
    }

    public function view_has_many()
    {
        $ret = [];

        $ret['Edit']['NumberRange']['class'] = 'NumberRange';
        $ret['Edit']['NumberRange']['relation'] = $this->numberranges;

        return $ret;
    }

    /**
     * Relationships:
     */
    public function sepaaccount()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\SepaAccount', 'sepaaccount_id');
    }

    public function items()
    {
        return $this->hasMany('Modules\BillingBase\Entities\Item');
    }

    public function numberranges()
    {
        return $this->hasMany('Modules\BillingBase\Entities\NumberRange', 'costcenter_id');
    }

    /**
     * Returns billing month with leading zero - Note: if not set June is set as default
     */
    public function get_billing_month()
    {
        return $this->billing_month ? str_pad($this->billing_month, 2, '0', STR_PAD_LEFT) : '06';
    }
}

class CostCenterObserver
{
    public function updated($costcenter)
    {
        $changes = $costcenter->getDirty();

        // Reset payed_month flag for all items belonging directly or indirectly to costcenter
        if (isset($changes['billing_month']) && $costcenter->getOriginal('billing_month') < $costcenter->billing_month) {
            $m = str_pad($costcenter->getOriginal('billing_month'), 2, '0', STR_PAD_LEFT);
            $filter = $m == 12 ? date('Y', strtotime('last year')).'-12' : date('Y')."-$m";

            // Note: Update doesnt work with Eloquent as automatically added 'updated_at'-column is ambigous
            $query = \DB::table('item')
                    ->join('contract as c', 'c.id', '=', 'item.contract_id')
                    ->join('product as p', 'item.product_id', '=', 'p.id')
                    ->where(function ($query) use ($costcenter) {
                        $query
                        ->where('item.costcenter_id', $costcenter->getOriginal('id'))
                        ->orWhere(function ($query) use ($costcenter) {
                            $query
                            ->where('p.costcenter_id', $costcenter->getOriginal('id'))
                            ->where('item.costcenter_id', 0);
                        })
                        ->orWhere(function ($query) use ($costcenter) {
                            $query
                            ->where('c.costcenter_id', $costcenter->getOriginal('id'))
                            ->where('p.costcenter_id', 0)
                            ->where('item.costcenter_id', 0);
                        });
                    })
                    ->where('p.billing_cycle', 'Yearly')
                    ->where('payed_month', $costcenter->getOriginal('billing_month'));

            // Log all updated items
            $items = implode(',', $query->pluck('item.id')->all());

            // Update
            $count = $query->update(['payed_month' => 0]);

            \Log::info("Changed billing month of CostCenter $costcenter->name [$costcenter->id] - Set payed_month column to 0 for $count items: $items");
        }
    }
}
