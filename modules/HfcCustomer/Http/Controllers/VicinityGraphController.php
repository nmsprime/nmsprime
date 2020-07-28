<?php

namespace Modules\HfcCustomer\Http\Controllers;

use View;
use App\Http\Controllers\BaseController;

class VicinityGraphController extends BaseController
{
    public function show($modemIds)
    {
        $title = 'Vicinity Graph';

        $ids = explode('+', $modemIds);
        $modems = \Modules\ProvBase\Entities\Modem::whereIn('id', $ids)->get();

        $tabs = (new \Modules\HfcCustomer\Http\Controllers\CustomerTopoController)->tabs($modems);

        return View::make('HfcBase::VicinityGraph.graph', $this->compact_prep_view(compact('title', 'tabs')));
    }
}
