<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Course;
use App\Models\CourseNote;
use App\Models\File;
use App\Models\Payment;
use App\Models\StudentNote;
use App\Models\TimeTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailsStudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// display File
    public function displayFile(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('name', 'course_id', 'type', 'subject_id'),
            [
                'name' => 'nullable|string|max:255',
                'course_id' => 'required|exists:courses,id',
                'type' => 'nullable|string|max:1|in:W,F',
                'subject_id' => 'nullable|exists:subjects,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $file = File::student()->with('worksheetsolves');

        $level_id = Course::find($request->course_id)->level_id;
        $file->where('level_id', $level_id);

        if ($request->type) {
            $file->where('type', $request->type);
        }
        if ($request->subject_id) {
            $file->where('subject_id', $request->subject_id);
        }
        if ($request->name) {
            $file->where('name', 'LIKE', '%' . $request->name . '%');
        }
        return $this->getResponse($file->latest()->paginate($limt));
    }

    //////////////// display Note Course
    public function displayNoteCourse(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id'),
            [
                'course_id' => 'required|exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = CourseNote::query();
        if ($request->course_id) {
            $note->where('course_id', $request->course_id);
        }

        return $this->getResponse($note->latest()->paginate($limt));
    }

    //////////////// display Program section
    public function displayProgramSection(Request $request)
    {

        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('section_id'),
            [
                'section_id' => 'required|exists:sections,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $days = TimeTable::where('section_id', $request->section_id)->with('table_details');
        return $this->getResponse($days->latest()->paginate($limt));
    }

    //////////////// display Note Student
    public function displayNoteStudent(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $note = StudentNote::query();
        $id=auth()->user()->id;
        $note->where('user_id', $id);

        return $this->getResponse($note->latest()->paginate($limt));
    }

    //////////////// display Payment
    public function displayPayment(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id'),
            [
                'course_id' => 'nullable|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user_id=auth()->user()->id;
        $note = Payment::query();
        $note->where('user_id', $user_id);
        if ($request->course_id) {
            $note->where('course_id', $request->course_id);
        }

        return $this->getResponse($note->latest()->paginate($limt));
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
}
