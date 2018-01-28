<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use App\Models\Membership;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    // use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'terms' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    protected function logout(Request $request){
        $request->session()->flush();
        \Auth::logout();
        return redirect('login');
    }

    public function membership(Request $request)
    {
        $token = @$request->token;
        $checkMembership = Membership::where('token',$token)->first();
        if($checkMembership){
            if($checkMembership->active){
                $request->session()->put('membership_token', $token);
                return redirect('login');
            }else{
                $previousUrl = app('url')->previous();
                $previousUrl = strtok($previousUrl,'?');
                $previousUrl .= '?'.http_build_query(['error' => 'Your membership is inactive or expired']);
                return redirect($previousUrl);
            }
        }else{
            $previousUrl = app('url')->previous();
            $previousUrl = strtok($previousUrl,'?');
            $previousUrl .= '?'.http_build_query(['error' => 'Your company was never registered before']);
            return redirect($previousUrl);
        }
    }
}
