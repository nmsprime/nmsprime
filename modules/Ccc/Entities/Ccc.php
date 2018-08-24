<?php

namespace Modules\Ccc\Entities;

class Ccc extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'ccc';

    public $guarded = ['template_filename_upload'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Ccc Config';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-users"></i>';
    }

    // link title in index view
    public function view_index_label()
    {
        return $this->view_headline();
    }
}
