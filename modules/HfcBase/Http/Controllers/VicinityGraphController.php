<?php

namespace Modules\HfcBase\Http\Controllers;

use View;
use App\Http\Controllers\BaseController;

class VicinityGraphController extends BaseController
{
    public function showGraph()
    {
        $title = 'Vicinity Graph';

        return View::make('HfcBase::VicinityGraph.graph', $this->compact_prep_view(compact('title')));
    }
}
