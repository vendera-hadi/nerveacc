<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Permission;
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

    // override kalo authenticated
    protected function authenticated(Request $request, $user)
    {
        // check user roles
        $check = \DB::table('user_has_roles')->where('user_id',$user->id)->first();
        if(empty($check)) Auth::logout();
        else $role_id = $check->role_id;
        // simpan all permissions
        $permissions = \DB::table('role_has_permissions')->where('role_id',$role_id)->pluck('permission_id')->toArray();
        $request->session()->put('permissions', $permissions);
        $request->session()->put('role', $role_id);
    }
}
