<?php

namespace Modules\PropertyManagement\Http\Controllers;

class ApartmentController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // $realties = selectList('realty', ['number', 'name'], true, ' - ');
        $realties = \DB::table('realty')->leftJoin('modem', 'modem.realty_id', 'realty.id')
            // ->leftJoin('contract', 'contract.realty_id', 'realty.id')
            // ->whereNull('contract.id')
            ->whereNull('modem.id')
            ->select('realty.*')
            ->get();

        $arr[null] = null;
        foreach ($realties as $realty) {
            $arr[$realty->id] = \Modules\PropertyManagement\Entities\Realty::labelFromData($realty);
        }

        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'select', 'name' => 'realty_id', 'description' => 'Realty', 'value' => $arr, 'space' => 1],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Number'],
            ['form_type' => 'text', 'name' => 'floor', 'description' => 'Floor', 'space' => 1],

            ['form_type' => 'text', 'name' => 'connection_type', 'description' => 'Connection type', 'autocomplete' => []],
            ['form_type' => 'checkbox', 'name' => 'connected', 'description' => trans('dt_header.apartment.connected')],
            ['form_type' => 'checkbox', 'name' => 'occupied', 'description' => trans('dt_header.apartment.occupied'), 'space' => 1],

            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        return $fields;
    }
}
