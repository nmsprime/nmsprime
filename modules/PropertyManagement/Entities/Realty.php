<?php

namespace Modules\PropertyManagement\Entities;

class Realty extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'realty';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name' => 'required',
            'street' => 'required',
            'house_nr' => 'required',
            'zip' => 'required',
            'city' => 'required',
            'agreement_from' => 'nullable|date',
            'agreement_to' => 'nullable|date',
            'last_restoration' => 'nullable|date',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Realty';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-building-o"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = 'success';

        if ($this->group_contract) {
            $bsclass = 'warning';
        } elseif ($this->concession_agreement) {
            $bsclass = 'info';
        }

        $label = $this->number ?: '';
        if ($label) {
            $label .= ' - ';
        }
        $label .= $this->name;

        return ['table' => $this->table,
                'index_header' => ["$this->table.name", 'number', 'street', 'house_nr', 'zip', 'city',
                    "$this->table.administration", 'expansion_degree', "$this->table.concession_agreement",
                    "$this->table.agreement_from", "$this->table.agreement_to", "$this->table.last_restoration", 'group_contract'
                    ],
                'header' => $label,
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
