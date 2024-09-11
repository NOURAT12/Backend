<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Achievements;
use App\Models\Ads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Absence
    public function createAbsence(Request $request)
    {

        $validate = Validator::make(
            $request->only('user_id', 'cause', 'late_time', 'course_id','date','type'),
            [
                'user_id' => 'required|exists:students,user_id',
                'late_time' => 'string|max:100',
                'cause' => 'string',
                'course_id' => 'required|exists:courses,id',
                'date' => 'required|date',
                'type' => 'required|string|max:1'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $absence = Absence::create([
            'course_id' => $request->course_id,
            'user_id' => $request->user_id,
            'date' => $request->date,
            'type' => $request->type,
            'late_time' => $request->late_time,
            'cause' => $request->cause
        ]);
        return $this->createResponse($absence);
    }

    //////////////// update Absence
    public function updateAbsence(Request $request)
    {

        $validate = Validator::make(
            $request->only('id','user_id', 'cause', 'late_time', 'course_id','date','type'),
            [
                'id' => 'required|exists:absences,id',
                'user_id' => 'required|exists:students,id',
                'late_time' => 'string|max:100',
                'cause' => 'string',
                'course_id' => 'required|exists:courses,id',
                'date' => 'required|date',
                'type' => 'required|string|max:1'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $absence = Absence::find();
        $absence->update([
            'course_id' => $request->course_id,
            'user_id' => $request->user_id,
            'date' => $request->date,
            'type' => $request->type,
            'late_time' => $request->late_time,
            'cause' => $request->cause
        ]);
        return $this->updateResponse($absence);
    }

    //////////////// delete Absence
    public function deleteAbsence(Request $request)
    {

        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:absences,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $absence = Absence::find();
        $absence->delete();
        return $this->deleteResponse($absence);
    }

    //////////////// display Absence
    public function displayAbsence(Request $request)
    {
        $limt= $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('user_id','course_id','type'),
            [
                'course_id' => 'exists:courses,id',
                'user_id' => 'exists:students,id',
                'type' => 'string|max:1|in:A,L'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $absence = Absence::query();
        if($request->user_id){
            $absence->where('user_id',$request->user_id);
        }
        if($request->course_id){
            $absence->where('course_id',$request->course_id);
        }
        if($request->type){
            $absence->where('type',$request->type);
        }
        return $this->getResponse($absence->latest()->paginate($limt));
    }
    //////////////// display Abs
    public function displayAbs(Request $request)
    {
        $limt= $request->limt ? $request->limt : 10;
        $absence = Ads::query();

        return $this->getResponse($absence->latest()->paginate($limt));
    }
    //////////////// display Ach
    public function displayAch(Request $request)
    {
        $limt= $request->limt ? $request->limt : 10;
        $absence = Achievements::query();
        return $this->getResponse($absence->latest()->paginate($limt));
    }
}
