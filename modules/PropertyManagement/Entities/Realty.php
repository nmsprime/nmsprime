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
            'node_id' => 'required',
            'name' => 'required',
            'street' => 'required',
            'house_nr' => 'required',
            'zip' => 'required',
            'city' => 'required',
            'agreement_from' => 'nullable|date',
            'agreement_to' => 'nullable|date',
            'last_restoration_on' => 'nullable|date',
        ];
    }

    public static function boot()
    {
        parent::boot();

        self::observe(new RealtyObserver);
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
                    "$this->table.agreement_from", "$this->table.agreement_to", "$this->table.last_restoration_on", 'group_contract',
                    ],
                'header' => $label,
                'bsclass' => $bsclass,
                // 'eager_loading' => ['contract'],
                // 'edit' => ['contract.firstname' => 'getContractFirstname'],
            ];
    }

    public function view_has_many()
    {
        $ret['Edit']['Apartment']['class'] = 'Apartment';
        $ret['Edit']['Apartment']['relation'] = $this->apartments;

        if (\Module::collections()->has('ProvBase')) {
            $ret['Edit']['Contract']['class'] = 'Contract';
            $ret['Edit']['Contract']['relation'] = $this->contracts;
        }

        return $ret;
    }

    public function view_belongs_to()
    {
        return $this->node;
    }

    /**
     * Relationships:
     */
    public function contracts()
    {
        return $this->HasMany(\Modules\ProvBase\Entities\Contract::class);
    }

    public function apartments()
    {
        return $this->HasMany(Apartment::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}

class RealtyObserver
{
    public function updated($realty)
    {
        $this->updateRelatedModelsAddress($realty);
    }

    /**
     * Update address of all related modems & contracts
     */
    private function updateRelatedModelsAddress($realty)
    {
        if (! \Module::collections()->has('ProvBase')) {
            return;
        }

        $diff = $realty->getDirty();

        if (! multi_array_key_exists(['street', 'house_nr', 'zip', 'city', 'district'], $diff)) {
            return;
        }

        // modem -> vertrag -> apartment -> realty
        // modem -> apartment -> realty
        // modem -> vertrag -> realty
        // modem -> realty
        // contract -> apartment -> realty
        // contract -> realty
        // Note: On extending the array by a new class this class must have the function updateAddressFromProperty()
        foreach (['Contract', 'Modem'] as $class) {
            $fqdn = "\Modules\ProvBase\Entities\\$class";
            $table = strtolower($class);

            $models = $fqdn::leftJoin('apartment as a', 'a.id', '=', "$table.apartment_id")
                ->where("$table.realty_id", $realty->id)
                ->orWhere('a.realty_id', $realty->id)
                ->select("$table.*")
                ->get();

            if ($class == 'Contract') {
                $modelsAll = $models;
            } else {
                $modelsAll = $modelsAll->merge($models);
            }
        }

        foreach ($modelsAll as $model) {
            $model->updateAddressFromProperty();
        }
    }
}
