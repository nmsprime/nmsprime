<?php

namespace Modules\PropertyManagement\Http\Controllers;

use View;
use Yajra\DataTables\DataTables;
use Modules\PropertyManagement\Entities\CutoffList;

class CutoffListController extends \BaseController
{
    /**
     * Separate index page with the list of all Apartments to cutoff
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
            $bsclass = 'info';

            if (strtotime($object->contract_end) < strtotime('-8 week')) {
                $bsclass = 'warning';
            }

            if (strtotime($object->contract_end) < strtotime('-12 week')) {
                $bsclass = 'danger';
            }

            return $bsclass;
        });

        $DT->editColumn('street', function ($object) {
            return '<a href="'.route('Apartment.edit', $object->id).'"><strong>'.
                \Modules\PropertyManagement\Entities\Apartment::view_icon().' '.$object->street.'</strong></a>';
        });

        $DT->rawColumns(['street']);

        return $DT->make();
    }
}
