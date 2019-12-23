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
                'number', 'floor',
                'connected', 'occupied', 'connection_type',
                'contract_end',
            ],
            'header' => "$this->number - $this->floor",
        ];
    }

    /**
     * Query for Connected Apartments with canceled Contracts and no new valid Contract
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function realtyApartmentQuery()
    {
        // SELECT: number, floor (A) | name (R) (int|string), connected (A+R), occupied (A+R), street (R),
        // house_nr (R), zip (R), city (R), district (R)

        // Possible new Contract of an Apartment related via Modem
        $newContractSubQuery = \Modules\ProvBase\Entities\Contract::join('modem', 'modem.contract_id', 'contract.id')
            ->join('apartment', 'modem.apartment_id', 'apartment.id')
            ->whereNull('contract.contract_end')
            ->whereNull('modem.deleted_at')->whereNull('apartment.deleted_at')
            ->select('contract.id', 'apartment.id as apartmentId');

        // All connected apartments with a canceled Contract via Modem
        $apartmentsSubQuery = Apartment::join('modem', 'modem.apartment_id', 'apartment.id')
            ->join('contract', 'contract.id', 'modem.contract_id')
            ->whereNull('modem.deleted_at')->whereNull('contract.deleted_at')
            ->where('apartment.connected', 1)
            ->where('contract.contract_start', '<', date('Y-m-d'))
            ->where('contract.contract_end', '<', date('Y-m-d'))
            ->select('apartment.*', 'contract.contract_end')
            ->groupBy('contract.id');

        // Possible new Contract of an Apartment
        $newDirectContractSubQuery = \Modules\ProvBase\Entities\Contract::join('apartment', 'contract.apartment_id', 'apartment.id')
            ->whereNull('apartment.deleted_at')
            ->whereNull('contract.contract_end')
            ->select('contract.id', 'apartment.id as apartmentId');

        // All connected Apartments having directly a canceled Contract
        $directContractApartmentSubQuery = Apartment::join('contract', 'contract.apartment_id', 'apartment.id')
            ->whereNull('contract.deleted_at')
            ->where('contract.contract_start', '<', date('Y-m-d'))
            ->where('contract.contract_end', '<', date('Y-m-d'))
            ->where('apartment.connected', 1)
            ->select('apartment.*', 'contract.contract_end')
            ->groupBy('contract.id');

        // All connected Apartments having a canceled Contract and no new valid Contract
        // (left joined by newContractSubQueryApartment and newContract.id must be null)
        $apartmentsQuery = \DB::table(\DB::raw("({$apartmentsSubQuery->toSql()}) as apartments"))
            ->mergeBindings($apartmentsSubQuery->getQuery())
            ->leftJoin(\DB::raw("({$newContractSubQuery->toSql()}) as newContract"), 'newContract.apartmentId', '=', 'apartments.id')
            ->mergeBindings($newContractSubQuery->getQuery())
            ->join('realty', 'realty.id', 'apartments.realty_id')
            ->whereNull('newContract.id')
            ->select('apartments.*', 'realty.street', 'realty.house_nr', 'realty.zip', 'realty.city', 'realty.district')
            ->groupBy('apartments.id');

        // All connected Apartments having directly a canceled Contract and no new valid Contract
        $directApartmentsQuery = \DB::table(\DB::raw("({$directContractApartmentSubQuery->toSql()}) as apartments"))
            ->mergeBindings($directContractApartmentSubQuery->getQuery())
            ->leftJoin(\DB::raw("({$newDirectContractSubQuery->toSql()}) as newContract"), 'newContract.apartmentId', '=', 'apartments.id')
            ->mergeBindings($newDirectContractSubQuery->getQuery())
            ->join('realty', 'realty.id', 'apartments.realty_id')
            ->whereNull('newContract.id')
            ->select('apartments.*', 'realty.street', 'realty.house_nr', 'realty.zip', 'realty.city', 'realty.district')
            ->groupBy('apartments.id');

        return $apartmentsQuery->union($directApartmentsQuery);
    }
}
