<?php

namespace Modules\PropertyManagement\Entities;

class Apartment extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'apartment';

    protected $casts = [
        'connected' => 'boolean',
        'occupied' => 'boolean',
    ];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'realty_id' => 'required',
            'number' => 'required',
            'floor' => 'required',
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

    public function view_has_many()
    {
        if (\Module::collections()->has('ProvBase')) {
            $ret['Edit']['Contract']['class'] = 'Contract';
            $ret['Edit']['Contract']['relation'] = $this->contract;
        }

        return $ret;
    }

    public function view_belongs_to()
    {
        return $this->realty;
    }

    /**
     * Relationships:
     */
    public function contract()
    {
        return $this->hasMany(\Modules\ProvBase\Entities\Contract::class);
    }

    public function realty()
    {
        return $this->belongsTo(Realty::class);
    }
}
