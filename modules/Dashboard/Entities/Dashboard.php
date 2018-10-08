<?php

namespace Modules\Dashboard\Entities;

use App\BaseModel;

class Dashboard extends BaseModel
{
//    protected $fillable = [];
    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-home"></i>';
    }
}
