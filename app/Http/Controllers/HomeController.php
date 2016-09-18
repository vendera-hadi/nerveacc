<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.2/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }

    public function contoh(){
        return view('contoh');   
    }

    public function contohget(Request $request){
        $test = '{"total":"1","rows":[{"id":"63443","firstname":"3423","lastname":"1","phone":"4234","email":"234@ww.sda"}]}';
        $test = json_decode($test);
        return response()->json($test);
    }

    public function contohinsert(Request $request){
        // var_dump($request->all());
        $test = array_merge(['id'=>2], $request->all());
        return response()->json($test);
    }

    public function contohupdate(Request $request){
        // var_dump($request->all());
        $test = $request->all();
        return response()->json($test);
    }

    public function contohdelete(Request $request){
        return response()->json(['success'=>true]);
    }
}