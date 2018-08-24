<?php

namespace Modules\VoipMon\Http\Controllers;

use Nwidart\Modules\Routing\Controller;

class VoipMonController extends Controller
{
    public function index()
    {
        return view('voipmon::index');
    }
}
