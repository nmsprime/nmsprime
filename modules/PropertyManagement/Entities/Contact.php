<?php

namespace Modules\PropertyManagement\Entities;

class Contact extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'contact';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // 'firstname1' => 'required',
            // 'lastname1' => 'required',
            'email1' => 'nullable|email',
            'email2' => 'nullable|email',
            'tel' => 'nullable|numeric',
            'tel_private' => 'nullable|numeric',
        ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Contact';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-address-card-o"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = 'success';

        if ($this->administration) {
            $bsclass = 'info';
        }

        return ['table' => $this->table,
            'index_header' => ["$this->table.firstname1", 'lastname1', 'firstname2', 'lastname2', 'company',
                'tel', 'tel_private', 'email1', 'email2',
                "$this->table.administration",
                'street', 'house_nr', 'zip', 'city', 'district',
            ],
            'header' => $this->label(),
            'bsclass' => $bsclass,
        ];
    }

    public function label()
    {
        return self::labelFromData($this);
    }

    public function view_has_many()
    {
        $ret['Edit']['Realty']['class'] = 'Realty';
        $ret['Edit']['Realty']['relation'] = $this->realties;

        $ret['Edit']['GroupContracts']['class'] = 'Contract';
        $ret['Edit']['GroupContracts']['relation'] = $this->contracts;

        if (\Module::collections()->has('Ticketsystem')) {
            $ret['Edit']['Ticket']['class'] = 'Ticket';
            $ret['Edit']['Ticket']['relation'] = $this->tickets;
        }

        return $ret;
    }

    /**
     * Relationships:
     */
    public function realties()
    {
        return $this->hasMany(Realty::class);
    }

    public function contracts()
    {
        return $this->hasMany(\Modules\ProvBase\Entities\Contract::class);
    }

    public function tickets()
    {
        return $this->hasMany(\Modules\Ticketsystem\Entities\Ticket::class);
    }

    public static function labelFromData($contact = null)
    {
        $label = $contact->company ? $contact->company.': ' : '';
        $label .= $contact->firstname1.' '.$contact->lastname1;
        $label .= $contact->firstname2 ? ', '.$contact->firstname2.' '.$contact->lastname2 : '';

        return $label;
    }
}
