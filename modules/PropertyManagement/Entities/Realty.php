<?php

namespace Modules\PropertyManagement\Entities;

class Realty extends \BaseModel
{
    use \App\Extensions\Geocoding\Geocoding;

    // The associated SQL table for this Model
    public $table = 'realty';

    public $guarded = ['apartmentCount'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'node_id' => 'required',
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
        } elseif ($this->apartments->isEmpty()) {
            $bsclass = 'active';
        }

        $label = self::labelFromData($this);

        return ['table' => $this->table,
                'index_header' => ['id', "$this->table.name", 'number', 'street', 'house_nr', 'zip', 'city',
                    "$this->table.contact_id", "$this->table.contact_local_id",
                    'expansion_degree', "$this->table.concession_agreement",
                    "$this->table.agreement_from", "$this->table.agreement_to", "$this->table.last_restoration_on", 'group_contract',
                    "$this->table.apartmentCountConnected", "$this->table.apartmentCount",
                    ],
                'header' => $label,
                'bsclass' => $bsclass,
                'eager_loading' => ['apartments'],
                'edit' => [
                    'apartmentCount' => 'getApartmentCount',
                    'apartmentCountConnected' => 'getConnectedApartmentCount',
                    'contact_id' => 'getContactName',
                    'contact_local_id' => 'getLocalContactName',
                ],
                'disable_sortsearch' => [
                    "$this->table.apartmentCount" => 'false',
                    "$this->table.apartmentCountConnected" => 'false',
                ],
                'filter' => [
                    // "$this->table.apartmentCount" => $this->apartmentCountQuery(),
                    // "$this->table.apartmentCountConnected" => ,
                    "$this->table.contact_id" => $this->contactFilterQuery(),
                    "$this->table.contact_local_id" => $this->localContactFilterQuery(),
                ],
            ];
    }

    public function view_has_many()
    {
        $ret['Edit']['Apartment']['class'] = 'Apartment';
        $ret['Edit']['Apartment']['relation'] = $this->apartments;

        if (\Module::collections()->has('ProvBase')) {
            $ret['Edit']['Modem']['class'] = 'Modem';
            $ret['Edit']['Modem']['relation'] = $this->modems;

            if ($this->apartments->isNotEmpty() || $this->group_contract) {
                $ret['Edit']['Modem']['options']['hide_create_button'] = 1;
                $ret['Edit']['Modem']['info'] = trans('propertymanagement::messages.realty.modemRelationInfo');
            }
            if ($this->modems->isNotEmpty()) {
                $ret['Edit']['Apartment']['options']['hide_create_button'] = 1;
                $ret['Edit']['Apartment']['info'] = trans('propertymanagement::messages.realty.apartmentRelationInfo');
            }

            if ($this->group_contract) {
                // Only 1 contract for group contracts
                $ret['Edit']['GroupContract']['class'] = 'Contract';
                if ($this->contract) {
                    $ret['Edit']['GroupContract']['relation'] = collect([$this->contract]);
                    $ret['Edit']['GroupContract']['options']['hide_create_button'] = 1;
                } else {
                    $ret['Edit']['GroupContract']['relation'] = collect();
                    $ret['Edit']['GroupContract']['options']['hide_delete_button'] = 1;
                }
            }

            // Show all indirectly related contracts as info
            $contracts = $this->getRelatedContracts(false);

            $ret['Edit']['ContractInfo']['class'] = 'Contract';
            $ret['Edit']['ContractInfo']['relation'] = $contracts;
            $ret['Edit']['ContractInfo']['options']['hide_create_button'] = 1;
            $ret['Edit']['ContractInfo']['options']['hide_delete_button'] = 1;
        }

        return $ret;
    }

    public function view_belongs_to()
    {
        return $this->node;
    }

    public function getContactName()
    {
        return $this->contact_id ? $this->contact->label() : null;
    }

    public function getLocalContactName()
    {
        return $this->contact_local_id ? $this->localContact->label() : null;
    }

    public function getApartmentCount()
    {
        return count($this->apartments);
    }

    public function getConnectedApartmentCount()
    {
        return count($this->apartments->where('connected', 1));
    }

    public function contactFilterQuery()
    {
        return ['query' => 'contact_id in (SELECT id from contact where contact.deleted_at is null and CONCAT(contact.firstname1, \' \', contact.lastname1) like ?)', 'eagers' => ['contact']];
    }

    public function localContactFilterQuery()
    {
        return ['query' => 'contact_local_id in (SELECT id from contact where contact.deleted_at is null and CONCAT(contact.firstname1, \' \', contact.lastname1) like ?)', 'eagers' => ['contact']];
    }

    /**
     * Relationships:
     */
    public function contract()
    {
        return $this->HasOne(\Modules\ProvBase\Entities\Contract::class);
    }

    public function apartments()
    {
        return $this->HasMany(Apartment::class);
    }

    public function modems()
    {
        return $this->HasMany(\Modules\ProvBase\Entities\Modem::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function localContact()
    {
        return $this->belongsTo(Contact::class, 'contact_local_id');
    }

    /**
     * Get all Contracts indirectly related via Modem or via Apartment -> Modem - and group contract if param set to true
     *
     * @param bool $withGroupContract, $withModems
     * @return Illuminate\Database\Eloquent\Collection of \Modules\ProvBase\Entities\Contract
     */
    public function getRelatedContracts($withGroupContract, $withModems = false)
    {
        $contracts1 = \Modules\ProvBase\Entities\Contract::join('modem as am', 'am.contract_id', 'contract.id')
            ->join('apartment', 'am.apartment_id', 'apartment.id')
            ->where('apartment.realty_id', $this->id)
            ->whereNull('apartment.deleted_at')
            ->whereNull('am.deleted_at')
            ->whereNull('contract.deleted_at')
            ->select('contract.*');

        $contracts2 = \Modules\ProvBase\Entities\Contract::join('modem as rm', 'rm.contract_id', 'contract.id')
            ->where('rm.realty_id', $this->id)
            ->whereNull('rm.deleted_at')
            ->whereNull('contract.deleted_at')
            ->select('contract.*');

        $contracts = $contracts2->union($contracts1);
        if ($withGroupContract) {
            $contracts3 = \Modules\ProvBase\Entities\Contract::where('realty_id', $this->id);

            $contracts = $contracts->union($contracts3);
        }

        if ($withModems) {
            $contracts = $contracts->with('modems');
        }

        return $contracts->get();
    }

    /**
     * Concatenate label from std class with realty data as returned from DB::table
     *
     * @param obj  $realty  PHP std class returned in collection from DB::table
     * @return string
     */
    public static function labelFromData($realty)
    {
        $label = $realty->number ? $realty->number.' - ' : '';
        $label .= $realty->street.' '.$realty->house_nr.', '.$realty->city;
        $label .= $realty->name ? ' ('.$realty->name.')' : '';

        return $label;
    }
}

class RealtyObserver
{
    public function creating($realty)
    {
        $realty->setGeocodes();
    }

    public function updating($realty)
    {
        $realty->setGeocodes();
    }

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

        // Models: realty->contract,  realty->modem, realty->apartment->modem
        $models = \Modules\ProvBase\Entities\Modem::leftJoin('apartment as a', 'a.id', '=', 'modem.apartment_id')
            ->whereNull('a.deleted_at')
            ->whereNull('modem.deleted_at')
            ->where(function ($query) use ($realty) {
                $query
                ->where('modem.realty_id', $realty->id)
                ->orWhere('a.realty_id', $realty->id);
            })
            ->select('modem.*')
            ->get();

        if ($realty->contract) {
            $models = $models->merge([$realty->contract]);
        }

        foreach ($models as $model) {
            $model->updateAddressFromProperty();
        }
    }
}
