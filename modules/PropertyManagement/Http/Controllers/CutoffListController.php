<?php

namespace Modules\PropertyManagement\Http\Controllers;

use View;
use Yajra\DataTables\DataTables;
use Modules\PropertyManagement\Entities\CutoffList;

class CutoffListController extends \BaseController
{
    /**
     * Separate index page for the resulting outstanding payments of each customer
     *
     * Here the all the customers with a sum unequal zero of all amounts and total fees of his debts are shown
     *
     * @return View
     */
    public function index()
    {
        $model = static::get_model_obj();
        $headline = trans('propertymanagement::view.cutoffList.headline');
        $view_header = \App\Http\Controllers\BaseViewController::translate_view('Overview', 'Header');
        $create_allowed = $delete_allowed = false;

        $view_path = 'Generic.index';
        $ajax_route_name = 'CutoffList.data';

        return View::make($view_path, $this->compact_prep_view(compact('headline', 'view_header', 'model', 'create_allowed', 'delete_allowed', 'ajax_route_name')));
    }

    public function index_datatables_ajax()
    {
        $request_query = CutoffList::realtyApartmentQuery();

        $DT = DataTables::make($request_query)->addColumn('responsive', '');

        $DT->setRowClass(function ($object) {
            return $object->type == trans('propertymanagement::view.apartment') ? '' : 'info';
        });

        $DT->editColumn('street', function ($object) {
            $apartment = $object->type == trans('propertymanagement::view.apartment') ? true : false;
            $routePrefix = $apartment ? 'Apartment' : 'Realty';
            $className = '\\Modules\\PropertyManagement\\Entities\\'.$routePrefix;

            return '<a href="'.route($routePrefix.'.edit', $object->apartmentId).'"><strong>'.
                $className::view_icon().' '.$object->street.'</strong></a>';
        });

        $DT->rawColumns(['street']);

        return $DT->make();
    }
}
