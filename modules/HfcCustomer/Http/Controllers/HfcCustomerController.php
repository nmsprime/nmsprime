<?php

namespace Modules\HfcCustomer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class HfcCustomerController extends Controller
{
    public function index()
    {
        return View::make('HfcCustomer::index');
    }
}
