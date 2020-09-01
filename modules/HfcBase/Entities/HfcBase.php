<?php

namespace Modules\HfcBase\Entities;

class HfcBase extends \BaseModel
{
    // The associated SQL table for this Model
    protected $table = 'hfcbase';

    // Add your validation rules here
    public function rules()
    {
        return [
        ];
    }

    // Name of View
    public static function view_headline()
    {
        return 'Hfc Base Config';
    }

    // link title in index view
    public function view_index_label()
    {
        return 'HfcBase';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-random"></i>';
    }
}
