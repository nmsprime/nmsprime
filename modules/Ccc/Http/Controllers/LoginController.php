<?php
namespace Modules\Ccc\Http\Controllers;

use Log, Module;
use Illuminate\Http\Request;
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
		$this->middleware('ccc', ['except' => 'logout']);
    }

	protected function guard()
	{
		return Auth::guard('ccc');
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
    public function showLoginForm()
    {
		$conf = Ccc::first();

		$prefix = 'customer';
		//$this->login_page = 'home'; // continue at ccc/home after successful login
        $head1 = $conf->headline1;
        $head2 = $conf->headline2;
        $image = 'main-pic-3.png';

        \App::setLocale(\App\Http\Controllers\BaseViewController::get_user_lang());

        return \View::make('auth.login', compact('head1', 'head2', 'prefix', 'image'));
	}
}
