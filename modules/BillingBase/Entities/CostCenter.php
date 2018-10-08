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
        return $this->billing_month ? ($this->billing_month > 9 ? $this->billing_month : '0'.$this->billing_month) : '06';
    }
}
