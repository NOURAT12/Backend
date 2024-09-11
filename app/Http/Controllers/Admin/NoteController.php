<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseNote;
use App\Models\Payment;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Note Course
    public function createNoteCourse(Request $request)
    {
        $validate = Validator::make(
            $request->only('note', 'course_id'),
            [
                'note' => 'required|string',
                'course_id' => 'required|exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = CourseNote::create([
            'note' => $request->note,
            'course_id' => $request->course_id,
        ]);

        return $this->createResponse($note);
    }

    //////////////// update Note Course
    public function updateNoteCourse(Request $request)
    {
        $validate = Validator::make(
            $request->only('note', 'course_id','id'),
            [
                'id'=>'required|exists:course_notes,id',
                'note' => 'required|string',
                'course_id' => 'required|exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = CourseNote::find($request->id);
        $note->update([
            'note' => $request->note,
            'course_id' => $request->course_id,
        ]);

        return $this->updateResponse($note);
    }

    //////////////// delete Note Course
    public function deleteNoteCourse(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id'=>'required|exists:course_notes,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = CourseNote::find($request->id);
        $note->delete();
        return $this->deleteResponse($note);
    }

    //////////////// display Note Course
    public function displayNoteCourse(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id'=>'exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = CourseNote::query();
        if($request->id){
            $note->where('course_id',$request->id);
        }

        return $this->getResponse($note->latest()->paginate($limt));
    }

 //////////////// create Note Student
 public function createNoteStudent(Request $request)
 {
     $validate = Validator::make(
         $request->only('note', 'user_id','course_id'),
         [
             'note' => 'required|string',
             'user_id' => 'required|exists:students,user_id',
             'course_id' => 'required|exists:courses,id',
         ]
     );
     if ($validate->fails()) {
         return $this->badResponse($validate);
     }
     $note = StudentNote::create([
         'note' => $request->note,
         'course_id' => $request->course_id,
         'user_id' => $request->user_id
     ]);
     $user = User::select('username','password')->where('id',$request->user_id)->first();
     $user->password = Crypt::decryptString($user->password);
     $auth = ['username'=> $user->username ,'password'=>$user->password];
     $sections= SectionStudent::where('user_id',$request->user_id)->get();
     $sections=Section::whereIn('id',$sections->pluck('section_id'))->get();
     $courses= Course::whereIn('id',$sections->pluck('course_id'))->get();
     $notes=StudentNote::with('course')->where('user_id',$request->user_id)->get();
     $payments=Payment::with('course')->where('user_id',$request->user_id)->orderBy('course_id')->latest()->get();
     for ($i=0; $i <count($courses) ; $i++) {
         for ($j=0; $j <count($sections) ; $j++) {
            if($sections[$j]->course_id==$courses[$i]->id){
             $courses[$i]->section=$sections[$j]->name;
            }
         }
     }
     ////////////NodeRel
     try {

         $response = Http::get(
             'http://'.'192.168.56.10'.':3000/send',
             [
                 'student_id' => $request->user_id,
                 'content'=>'تم اضافة ملاحظة جديدة'
             ]
         );

     } catch (\Throwable $th) {
        return $th;
     }
     $data=[
     "courses"=>$courses,
     "notes"=>$notes,
     "payments"=>$payments,
     "auth"=>$auth,
     ];

     return $this->createResponse($data);
 }

    //////////////// update Note Student
    public function updateNoteStudent(Request $request)
    {
        $validate = Validator::make(
            $request->only('note', 'user_id','course_id','id'),
            [
                'note' => 'required|string',
                'id' => 'required|exists:student_notes,id',
                'user_id' => 'required|exists:students,user_id',
                'course_id' => 'required|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = StudentNote::find($request->id);
        $note->update([
            'note' => $request->note,
            'course_id' => $request->course_id,
            'user_id' => $request->user_id
        ]);

        return $this->updateResponse($note);
    }

    //////////////// delete Note Student
    public function deleteNoteStudent(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:student_notes,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = StudentNote::find($request->id);
        $note->delete();
        return $this->deleteResponse($note);
    }

    //////////////// display Note Student
    public function displayNoteStudent(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id'=>'exists:students,user_id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = StudentNote::query();
        if($request->id){
            $note->where('user_id',$request->id);
        }

        return $this->getResponse($note->latest()->paginate($limt));
    }
}
