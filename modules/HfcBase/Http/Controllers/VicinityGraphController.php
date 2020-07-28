<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
use App\Http\Controllers\BaseController;

class VicinityGraphController extends BaseController
{
    public function show()
    {
        $title = 'Vicinity Graph';
        //Static values, demo only
        $field = 'id';
        $search = 2;

        $tabs = TreeErdController::getTabs($field, $search);

        return View::make('HfcBase::VicinityGraph.graph', $this->compact_prep_view(compact('title', 'tabs')));
    }
}
