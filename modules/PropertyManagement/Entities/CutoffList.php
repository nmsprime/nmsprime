<?php

namespace Modules\PropertyManagement\Entities;

class CutoffList extends \BaseModel
{
    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Cut off list';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-chain-broken"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return [
            'table' => '',
            'index_header' => [
                'street', 'house_nr', 'zip', 'city', 'district',
                'number', 'floor', 'type',
                'connected', 'occupied', 'connection_type',
                'contract_end'
            ],
            'header' => "$this->number - $this->floor",
        ];
    }

    /**
     * Query for Connected Apartments + Realties with canceled Contracts and no new valid Contract
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function realtyApartmentQuery()
    {
        // SELECT: number, floor (A) | name (R) (int|string), connected (A+R), occupied (A+R), street (R),
        // house_nr (R), zip (R), city (R), district (R)

        // Possible new Contract of an Apartment
        $newContractSubQueryApartment = \Modules\ProvBase\Entities\Contract::join('modem', 'modem.contract_id', 'contract.id')
            ->join('apartment', 'modem.apartment_id', 'apartment.id')
            ->whereNull('contract.contract_end')
            ->whereNull('modem.deleted_at')->whereNull('contract.deleted_at')->whereNull('apartment.deleted_at')
            ->select('contract.id', 'apartment.id as apartmentId');

        // Possible new Contract of a Realty
        $newContractSubQueryRealty = \Modules\ProvBase\Entities\Contract::join('realty', 'contract.realty_id', 'realty.id')
            ->where('group_contract', 1)
            ->whereNull('contract.contract_end')
            ->whereNull('contract.deleted_at')->whereNull('realty.deleted_at')
            ->select('contract.id', 'realty.id as realtyId');

        // We have to specify all columns here as realty.* conflicts with apartment.connected|occupied|connection_type
        $selectArr = [
            'realty.id',
            'realty.created_at',
            'realty.updated_at',
            'realty.deleted_at',
            'realty.node_id',
            'realty.name',
            'realty.street',
            'realty.house_nr',
            'realty.district',
            'realty.zip',
            'realty.city',
            'realty.administration',
            'realty.expansion_degree',
            'realty.concession_agreement',
            'realty.agreement_from',
            'realty.agreement_to',
            'realty.last_restoration_on',
            'realty.group_contract',
            'realty.description',
            'realty.contact_id',
            'realty.contact_local_id',
            'realty.x',
            'realty.y',
            'realty.geocode_source',
            'realty.country_code',

            'modem.id as modemId',
            'contract.id as cId',
            'contract.contract_end',
        ];

        $type['apartment'] = trans('propertymanagement::view.apartment');
        $type['realty'] = trans('propertymanagement::view.realty.');

        // All connected apartments with a canceled Contract
        $apartmentsSubQuery = Apartment::join('modem', 'modem.apartment_id', 'apartment.id')
            ->join('realty', 'realty.id', 'apartment.realty_id')
            ->join('contract', 'contract.id', 'modem.contract_id')
            ->whereNull('modem.deleted_at')->whereNull('contract.deleted_at')->whereNull('apartment.deleted_at')
            ->where('apartment.connected', 1)
            ->where('contract.contract_start', '<', date('Y-m-d'))
            ->where('contract.contract_end', '<', date('Y-m-d'))
            ->select(array_merge($selectArr, [
                'apartment.connected', 'apartment.occupied', 'apartment.connection_type', 'apartment.number',
                \DB::raw("'{$type['apartment']}' as type"), \DB::raw('CAST(floor as CHAR(10)) as floor'), 'apartment.id as apartmentId'
            ]))
            ->groupBy('contract.id');

        // All connected realties with group contract set having a canceled Contract
        $realtiesSubQuery = Realty::join('modem', 'modem.realty_id', 'realty.id')
            ->join('contract', 'contract.id', 'modem.contract_id')
            ->where('contract.contract_start', '<', date('Y-m-d'))
            ->where('contract.contract_end', '<', date('Y-m-d'))
            ->where('realty.connected', 1)
            ->select(array_merge($selectArr, [
                'realty.connected', 'realty.occupied', 'realty.connection_type', 'realty.number as number',
                \DB::raw("'{$type['realty']}' as type"), \DB::raw('NULL as floor'), 'realty.id as realtyId'
            ]))
            ->groupBy('contract.id');

        // All connected apartments having a canceled contract and no new valid contract
        // (left joined by newContractSubQueryApartment and newContract.id must be null)
        $apartmentsQuery = \DB::table(\DB::raw("({$apartmentsSubQuery->toSql()}) as apartments"))
            ->mergeBindings($apartmentsSubQuery->getQuery())
            ->select('apartments.*')
            ->leftJoin(\DB::raw("({$newContractSubQueryApartment->toSql()}) as newContract"), 'newContract.apartmentId', '=', 'apartments.apartmentId')
            ->mergeBindings($newContractSubQueryApartment->getQuery())
            ->whereNull('newContract.id')
            ->groupBy('apartments.apartmentId')
            ->orderBy('apartments.street')->orderBy('apartments.house_nr');

        // All connected realties having a canceled contract and no new valid contract
        // (left joined by newContractSubQueryRealty and newContract.id must be null)
        $realtiesQuery = \DB::table(\DB::raw("({$realtiesSubQuery->toSql()}) as realties"))
            ->mergeBindings($realtiesSubQuery->getQuery())
            ->select('realties.*')
            ->leftJoin(\DB::raw("({$newContractSubQueryRealty->toSql()}) as newContract"), 'newContract.realtyId', '=', 'realties.realtyId')
            ->mergeBindings($newContractSubQueryRealty->getQuery())
            ->whereNull('newContract.id')
            ->groupBy('realties.realtyId')
            ->orderBy('realties.street')->orderBy('realties.house_nr');

        return $apartmentsQuery->union($realtiesQuery);
    }
}
