<?php

namespace Modules\PropertyManagement\Entities;

class Apartment extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'apartment';

    // protected $casts = [
    //     'connected' => 'boolean',
    //     'occupied' => 'boolean',
    // ];

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
                'index_header' => ['realty.street', 'realty.house_nr', 'realty.zip', 'realty.city', 'realty.district',
                    "$this->table.number", 'floor',
                    "$this->table.connected", "$this->table.occupied", 'connection_type',
                ],
                'header' => "$this->number - $this->floor",
                'bsclass' => $bsclass,
                'eager_loading' => ['realty'],
            ];
    }

    public function view_has_many()
    {
        if (\Module::collections()->has('ProvBase')) {
            $ret['Edit']['Modem']['class'] = 'Modem';
            $ret['Edit']['Modem']['relation'] = $this->modems;
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
        return $this->hasOne(\Modules\ProvBase\Entities\Contract::class);
    }

    public function modems()
    {
        return $this->HasMany(\Modules\ProvBase\Entities\Modem::class);
    }

    public function realty()
    {
        return $this->belongsTo(Realty::class);
    }

    public static function labelFromData($apartment)
    {
        // Adresse von Liegenschaft + Etage + Nummer
        // Note realty data must be joined
        return $apartment->street.' '.$apartment->house_nr.', '.$apartment->city.' - '.$apartment->number.' ('.$apartment->floor.')';
    }
}
