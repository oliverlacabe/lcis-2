<?php

namespace App\Http\Controllers\Instructor;

use DB;
use Auth;
use App\Library\Api;
use App\Http\Requests;
use App\Classallocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Instructor_Class extends Controller
{
    public $system;

    public function __construct()
    {
        $this->middleware('auth');
        $this->system = Api::systemValue();
    }

    public function index()
    {
        $data['classes'] = Classallocation::where('academicterm', $this->system->currentacademicterm)
                        ->where('instructor', Auth::user()->id)->get();
        $data['system'] = $this->system;

        return view(Api::getView(), $data);
    }
}
