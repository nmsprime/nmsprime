<?php

namespace Modules\ProvBase\Entities;

class Qos extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'qos';

    // Add your validation rules here
    public function rules()
    {
        return [
            'name' => "required|unique:qos,name,{$this->id},id,deleted_at,NULL",
            'ds_rate_max' => 'required|numeric|min:0',
            'us_rate_max' => 'required|numeric|min:0',
        ];
    }

    /**
     * Relations
     */
    public function modem()
    {
        return $this->hasMany(Modem::class);
    }

    public function prices()
    {
        return $this->hasMany(\Modules\BillingBase\Entities\Price::class);
    }

    public function radgroupreplies()
    {
        return $this->hasMany(RadGroupReply::class, 'groupname');
    }

    public function products()
    {
        return $this->hasMany(\Modules\BillingBase\Entities\Product::class);
    }

    // Name of View
    public static function view_headline()
    {
        return 'Qos';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-ticket"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();

        return ['table' => $this->table,
            'index_header' => [$this->table.'.name', $this->table.'.ds_rate_max', $this->table.'.us_rate_max'],
            'header' =>  $this->name,
            'bsclass' => $bsclass,
            'edit' => ['ds_rate_max' => 'unit_ds_rate_max', 'us_rate_max' => 'unit_us_rate_max'],
            'order_by' => ['0' => 'asc'], ];
    }

    public function get_bsclass()
    {
        $bsclass = 'success';

        return $bsclass;
    }

    public function unit_ds_rate_max()
    {
        return $this->ds_rate_max.' MBit/s';
    }

    public function unit_us_rate_max()
    {
        return $this->us_rate_max.' MBit/s';
    }

    public static function setIndexDeleteTitle()
    {
        return trans('messages.indexDeleteDisabledTitle', ['relation' => trans('messages.Product')]);
    }

    public function set_index_delete()
    {
        if (! \Module::collections()->has('BillingBase')) {
            return false;
        }

        $relatedProducts = $this->products()->whereNull('product.deleted_at')->count();

        return $this->index_delete_disabled = $relatedProducts >= 0;
    }

    /**
     * BOOT: init quality observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new \Modules\ProvBase\Observers\QosObserver);
    }
}
