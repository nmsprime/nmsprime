<?php

namespace Modules\PropertyManagement\Http\Controllers;

use App\Http\Controllers\BaseViewController;
use Modules\PropertyManagement\Entities\Contact;

class RealtyController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $nodes = selectList('node', 'name', true);

        $contactTypes['administration'] = \DB::table('contact')->whereNull('deleted_at')->where('administration', 1)->get();
        $contactTypes['local'] = \DB::table('contact')->whereNull('deleted_at')->where('administration', 0)->get();

        $contactArr = ['administration' => [null => null], 'local' => [null => null]];
        foreach ($contactTypes as $key => $contacts) {
            foreach ($contacts as $contact) {
                $contactArr[$key][$contact->id] = Contact::labelFromData($contact);
            }
        }

        // label has to be the same like column in sql table
        $fields1 = [
            ['form_type' => 'select', 'name' => 'node_id', 'description' => trans('propertymanagement::view.Node'), 'value' => $nodes, 'space' => 1],

            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Number', 'space' => 1],
            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'house_nr', 'description' => 'House number'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'district', 'description' => 'District', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'country_code', 'description' => 'Country code', 'help' => trans('helper.countryCode')],
            ['form_type' => 'html', 'name' => 'geopos', 'description' => trans('messages.geopos_x_y'), 'html' => BaseViewController::geoPosFields($model)],
            ['form_type' => 'text', 'name' => 'geocode_source', 'description' => 'Geocode origin', 'help' => trans('helper.Modem_GeocodeOrigin'), 'space' => 1],
        ];

        $fields2 = [];
        if (! $model->id || ($model->id && $model->apartments->isEmpty())) {
            $fields2 = [
                ['form_type' => 'text', 'name' => 'connection_type', 'description' => 'Connection type', 'autocomplete' => []],
                ['form_type' => 'checkbox', 'name' => 'connected', 'description' => trans('dt_header.apartment.connected')],
                ['form_type' => 'checkbox', 'name' => 'occupied', 'description' => trans('dt_header.apartment.occupied'), 'space' => 1],
            ];
        }

        $fields3 = [
            ['form_type' => 'checkbox', 'name' => 'group_contract', 'description' => trans('propertymanagement::view.group_contract')],
            ['form_type' => 'checkbox', 'name' => 'concession_agreement', 'description' => trans('propertymanagement::view.realty.concession_agreement')],
            ['form_type' => 'text', 'name' => 'agreement_from', 'description' => trans('dt_header.realty.agreement_from'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'agreement_to', 'description' => trans('dt_header.realty.agreement_to'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'last_restoration_on', 'description' => trans('dt_header.realty.last_restoration_on')],
            ['form_type' => 'text', 'name' => 'expansion_degree', 'description' => trans('dt_header.expansion_degree'), 'space' => 1],

            ['form_type' => 'select', 'name' => 'contact_id', 'value' => $contactArr['administration'], 'description' => trans('dt_header.realty.contact_id')],
            ['form_type' => 'select', 'name' => 'contact_local_id', 'value' => $contactArr['local'], 'description' => trans('dt_header.realty.contact_local_id'), 'space' => 1],
        ];

        if ($model->id) {
            $model->apartmentCount = $model->apartments->where('connected', 1)->count().' / '.$model->apartments->count();

            $fields3[] = ['form_type' => 'text', 'name' => 'apartmentCount', 'description' => trans('propertymanagement::view.realty.apartmentCount'), 'options' => ['readonly']];
        }

        $fields3[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return array_merge($fields1, $fields2, $fields3);
    }
}
