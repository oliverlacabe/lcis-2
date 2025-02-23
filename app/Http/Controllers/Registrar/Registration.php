<?php

namespace App\Http\Controllers\Registrar;

use DB;
use Sesion;
use App\Party;
use App\Course;
use App\Library\Api;
use App\Http\Requests;
use App\Library\Curriculum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Registration as ModelRegistration;
use App\Http\Requests\NewStudentRegRequest;

// TODO: change this class to new_student_registration
class Registration extends Controller
{
    public $system;

    // status for registration
    // P = Pending
    // A = Approved
    // D = Disapprove

    function __construct()
    {
        $this->middleware('auth');
        $this->system = Api::systemValue();
    }

    public function new_student()
    {
        $data['courses']    = Course::all();
        $data['majors']     = DB::table('tbl_major')->get();
        $data['religions']  = DB::table('tbl_religion')->get();

        return view(Api::getView(), $data);

    }

    public function register_new_student(NewStudentRegRequest $request)
    {
        DB::transaction(function () {
            $party                  = new Party;
            $party->firstname       = $request->firstname;
            $party->lastname        = $request->lastname;
            $party->middlename      = $request->middlename;
            $party->sex             = $request->gender;
            $party->placeofbirth    = $request->pob;
            $party->dateofbirth     = $request->dob;
            $party->mobilenumber    = $request->contact;
            $party->religion        = $request->religion;
            $party->emailaddress    = $request->emailadd;
            $party->civilstatus     = $request->maritalstatus;
            $party->legacyid        = $this->system->laststudentid;
            $party->address1        = $request->mailing_add;

            // insert into party
            $party->save();

            // get the inserted id
            $party_id               = $party->id;

            // set the laststudentid
            $this->setLastStudentId();

            $curriculum = Curriculum::getCurrentCurriculum($request->course, $request->major, $this->system);

            $coursemajor    = DB::table('coursemajor')->where('course', $request->course)
                            ->where('major', $request->major)->first();

            $registration               = new ModelRegistration;
            $registration->curriculum   = $curriculum;
            $registration->student      = $party_id;
            $registration->academicterm = $this->system->currentacademicterm;
            $registration->status       = 'P';
            $registration->date         = date('Y-m-d');
            $registration->coursemajor  = $coursemajor->id;

            // insert into registration
            $registration->save();

            DB::table('tbl_student')->insert([
                'id'                => $party_id,
                'fathername'        => $request->father_name,
                'fatherproffession' => $request->father_occupation,
                'mothername'        => $request->mother_name,
                'motherproffession' => $request->mother_occupation
            ]);

            DB::table('tbl_useraccess')->insert([
                'id'        => $party_id,
                'username'  => $request->username,
                'password'  => password_hash($request->password, PASSWORD_BCRYPT)
            ]);

            // TODO: insert into tbl_useroption

        });

        Session::put('message', htmlAlert('Successfully Registered', 'success'));

        return back();
    }

    public function setLastStudentId()
    {
        $lastStudent    = explode('-', $this->system->laststudentid);
        $increment      = $lastStudent[1] + 1;
        $newStudent     = $lastStudent[0].'-'.$increment;

        DB::table('tbl_systemvalues')->update(['laststudentid' => $newStudent]);
    }
}
