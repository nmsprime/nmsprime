<?php

namespace Modules\PropertyManagement\Entities;

class Node extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'node';

    protected $casts = [
        'headend' => 'boolean',
    ];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name' => 'required',
            'street' => 'required',
            'house_nr' => 'required',
            'zip' => 'required',
            'city' => 'required',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Node';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-share-alt-square"></i>';
        // return '<i class="fa fa-caret-square-o-right"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->headend ? 'success' : 'info';

        return ['table' => $this->table,
                'index_header' => ['node.name', 'street', 'house_nr', 'zip', 'city', "$this->table.type", "$this->table.headend"],
                'header' => $this->name,
                'bsclass' => $bsclass,
                // 'eager_loading' => ['contract'],
                // 'edit' => ['contract.firstname' => 'getContractFirstname'],
            ];
    }

    public function view_has_many()
    {
        $rel['Edit']['Realty']['class'] = 'Realty';
        $rel['Edit']['Realty']['relation'] = $this->realties;

        return $rel;
    }

    /**
     * Relationships:
     */
    public function realties()
    {
        return $this->HasMany(Realty::class);
    }
}
