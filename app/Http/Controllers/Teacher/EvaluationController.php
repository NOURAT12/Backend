<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseTeacher;
use App\Models\Evaluation;
use App\Models\Lecture;
use App\Models\SectionStudent;
use App\Models\SectionTeacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

     //////////////// create Evaluation
     public function createEvaluation(Request $request)
     {
         $validate = Validator::make(
             $request->only('course_id', 'lecture_id', 'user_ids', 'subject_id', 'values'),
             [
                 'subject_id' => 'required|exists:subjects,id',
                 'course_id' => 'required|exists:courses,id',
                 'lecture_id' => 'required|exists:lectures,id',
                 'user_ids' => 'required|array|min:1',
                 'user_ids.*' => 'exists:students,user_id',
                 'values' => 'required|array|min:1',
                 'values.*' => 'string|max:1',
             ]
         );
         if ($validate->fails()) {
             return $this->badResponse($validate);
         }
         if (count($request->values) != count($request->user_ids)) {
             return $this->failResponse('يجب ان يكون حجم المصفوفتين متساويين');
         }

        DB::beginTransaction(); 
         try {
             for ($i = 0; $i < count($request->values); $i++) {
                 $evaluation = Evaluation::create([
                     'course_id' => $request->course_id,
                     'user_id' => $request->user_ids[$i],
                     'lecture_id' => $request->lecture_id,
                     'subject_id' => $request->subject_id,
                     'created_by' =>auth()->user()->id,
                     'value' => $request->values[$i]
                 ]);
             }
             DB::commit();
         } catch (\Throwable $th) {
             DB::rollBack();
             return $this->serverResponse();
         }

         return $this->createResponse($evaluation);
     }

     //////////////// update Evaluation
     public function updateEvaluation(Request $request)
     {
         $validate = Validator::make(
             $request->only('id', 'lecture_id', 'user_id', 'subject_id', 'value'),
             [
                 'id' => 'required|exists:evaluations,id',
                 'subject_id' => 'required|exists:subjects,id',
                 'lecture_id' => 'required|exists:lectures,id',
                 'user_id' => 'required|exists:students,user_id',
                 'value' => 'required|string|max:1|in:A,B,C,D,E'
             ]
         );
         if ($validate->fails()) {
             return $this->badResponse($validate);
         }

         $evaluation = Evaluation::find($request->id);
         $evaluation->update([
             'user_id' => $request->user_id,
             'lecture_id' => $request->lecture_id,
             'subject_id' => $request->subject_id,
             'value' => $request->value
         ]);
         return $this->updateResponse($evaluation);
     }

     //////////////// delete Evaluation
     public function deleteEvaluation(Request $request)
     {
         $validate = Validator::make(
             $request->only('id'),
             [
                 'id' => 'required|exists:evaluations,id'
             ]
         );
         if ($validate->fails()) {
             return $this->badResponse($validate);
         }
         $evaluation = Evaluation::find($request->id);
         $evaluation->delete();
         return $this->deleteResponse($evaluation);
     }

    //////////////// display Evaluation
    public function displayEvaluation(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('section_id','user_id'),
            [
                'section_id' => 'exists:sections,id',
                'user_id' => 'exists:students,user_id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $eva = Evaluation::query();
        $eva->where('created_by',auth()->user()->id);
        if ($request->section_id) {
            $ids=SectionStudent::where('section_id',$request->section_id)->get()->pluck('user_id');
            $eva->whereIn('user_id', $ids)->paginate($limt);
        }
        if ($request->user_id) {
            $eva->where('user_id', $request->user_id);
        }

        return $this->getResponse($eva->latest()->paginate($limt));
    }

}
