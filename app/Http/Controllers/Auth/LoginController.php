<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Permission;
use App\Models\MsCompany;
use App\Models\Membership;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    // override login page
    protected function showLoginForm()
    {

        return view('auth.login');
    }

    public function login(Request $request)
    {
        if(!\Session::has('membership_token')) return redirect('login')->with('error','You can\'t Login before signin from JLM website');
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    // override kalo authenticated
    protected function authenticated(Request $request, $user)
    {
        // check user roles
        $check = \DB::table('user_has_roles')->where('user_id',$user->id)->first();
        if(empty($check)) Auth::logout();
        else $role_id = $check->role_id;
        // simpan all permissions
        $permissions = \DB::table('role_has_permissions')->where('role_id',$role_id)->pluck('permission_id')->toArray();
        $company_detail =  MsCompany::first();
        $request->session()->put('permissions', $permissions);
        $request->session()->put('role', $role_id);
        $request->session()->put('company_title', $company_detail->title);
    }
}
