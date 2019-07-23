<?php

namespace Modules\PropertyManagement\Entities;

class Apartment extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'apartment';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'number' => 'required',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Apartment';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-bed"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = 'success';

        return ['table' => $this->table,
                'index_header' => ["$this->table.number", 'floor', "$this->table.connected", "$this->table.occupied"],
                'header' => "$this->number - $this->floor",
                'bsclass' => $bsclass,
                // 'eager_loading' => ['contract'],
                // 'edit' => ['contract.firstname' => 'getContractFirstname'],
            ];
    }

    /**
     * Relationships:
     */
    // public function contract()
    // {
    //     return $this->belongsTo(Contract::class);
    // }
}
