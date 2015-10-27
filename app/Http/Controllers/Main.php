<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Session;
use Validator;
use App\Party;
use App\Library\Api;
use App\User_access;
use App\Academicterm;

class Main extends Controller
{
    function index(Request $request)
    {
        if ( !Auth::guest()) {
            $user = Party::find(Auth::user()->id);
            
            return view('home', ['user' => $user]);
        } else 
            return $this->login($request);
    }

    function login($request)
    {
        $username = $request->username;
        $password = $request->password;

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
            ]);

        if ( !$validator->fails()) {

            if (Auth::attempt(['username' => $username, 'password' => $password]))
                return redirect('/');
            else 
                return view('index', ['error' => htmlAlert('Authentication Failed')]);

        } else 
            return view('index', ['error' => '']);
    }

    function logout()
    {
        Auth::logout();
        Session::flush();

        return redirect('/');
    }
}
