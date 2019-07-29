<?php

namespace Modules\BillingBase\Entities;

class BillingBase extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'billingbase';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // 'rcd' 	=> 'numeric|between:1,28',
            'cdr_offset'						=> 'nullable|numeric|between:0,11',
            'tax' 								=> 'nullable|numeric|between:0,100',
            'voip_extracharge_default' 			=> 'nullable|numeric',
            'voip_extracharge_mobile_national' 	=> 'nullable|numeric',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Billing Config';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-line-chart"></i>';
    }

    // link title in index view
    public function view_index_label()
    {
        return $this->view_headline();
    }

    public static function contactPersons()
    {
        $filepath = storage_path('app/config/billingbase/ags.php');

        if (! is_file($filepath)) {
            \Log::error("Missing list of Antennengemeinschaft contacts under $filepath");

            return [0 => null];
        }

        return require $filepath;
    }
}
