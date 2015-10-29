<?php

namespace App\Http\Controllers\Edp;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use App\Library\Api;

class ClassAlloc extends Controller
{
    public $system;

    function __construct()
    {
    	$this->system = Api::systemValue();
        $this->middleware('auth');
    }

    function init()
    {
    	$data['val'] = '';
    	if ($this->system->phaseterm == env('FIN')) {
            $stat = $this->system->classallocationstatus;

    		if ($stat == 2 OR $stat == 1) {
    			$c		= DB::table('tbl_completion')->where('stage', 2)
							->where('academicterm', $system->phaseterm)
							->where('status', 'O');

    			if ($c == env('COLLEGE_COUNT')) {
    				// update the systemvalues
					DB::table('tbl_systemvalues')->update(['classallocationstatus' => 3]);
					// delete classallocation for this phaseterm
					DB::table('tbl_classallocation')->where('academicterm', $system->phaseterm)
						->delete();
					$sec = DB::table('out_section')->get();

					foreach ($sec as $section) {

						// if the section is zero it will not satisfy this condition
						for ($i = 1; $i <= $section->section; $i++) {
							$data['academicterm'] 	= $section->academicterm;
							$data['coursemajor'] 	= $section->coursemajor;
							$data['subject'] 		= $section->subject;
							$data['instructor']		= 0;
							$data['reserved']		= 0;
							$data['enrolled']		= 0;
							DB::table('tbl_classallocation')->insert($data);
						}
					}

					$data['val'] = 'OK';
    			} else
    				$data['val'] = 'college count';

    		} else
    			$data['val'] = 'cannot run';

    	} else
    		$data['val'] = 'cannot run in this phase';
    	
    	return view('edp.initClass', $data);
    }
}
