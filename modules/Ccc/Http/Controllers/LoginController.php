<?php

namespace Modules\Ccc\Http\Controllers;

use App;
use Session;
use Modules\Ccc\Entities\Ccc;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * This class inherits all the show(), login(), logout() stuff
 *
 * @author Christian Schramm
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('cccRedirect', ['except' => 'logout']);
    }

    /**
     * Return a instance of the used guard
     */
    protected function guard()
    {
        return Auth::guard('ccc');
    }

    /**
     * Change Login Check Field to login_name
     * Laravel Standard: email
     */
    public function username()
    {
        return 'login_name';
    }

    /**
     * Show Login Page
     *
     * @return type view
     */
    public function showLoginForm()
    {
        $conf = Ccc::first();

        $prefix = 'customer';
        $head1 = $conf->headline1;
        $head2 = $conf->headline2;
        $image = 'main-pic-3.png';

        Session::put('ccc-language', $conf->language);

        return \View::make('auth.login', compact('head1', 'head2', 'prefix', 'image'));
    }

    /**
     * Show Default Page after successful login
     *
     * TODO: Redirect to a global overview page
     *
     * @return type Redirect
     */
    private function redirectTo()
    {
        return 'customer';
    }
}
