<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Lecture;
use App\Models\SectionStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EvaluatioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

//////////////// create Evaluation
public function createEvaluation(Request $request)
{
    $validate = Validator::make(
        $request->only( 'lecture_id', 'user_ids', 'values'),
        [
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
    $lecture=Lecture::where('id',$request->lecture_id)->first();
    $evaluations = [];
    DB::beginTransaction();
    try {
        for ($i = 0; $i < count($request->values); $i++) {
            $evaluations[$i] = Evaluation::create([
                'user_id' => $request->user_ids[$i],
                'lecture_id' => $request->lecture_id,
                'created_by' =>auth()->user()->id,
                'value' => $request->values[$i],
                'course_id' => $lecture->course_id,
                'subject_id' => $lecture->subject_id
            ]);
        }
        DB::commit();
    } catch (\Throwable $th) {
        DB::rollBack();
        return $this->serverResponse();
    }
////////////NodeRel
   for ($i=0; $i <count($request->user_ids) ; $i++) {
    try {

        $response = Http::get(
            'http://'.'192.168.56.10'.':3000/send',
            [
                'student_id' => $request->user_ids[$i],
                'content'=>'تم اضافة تقييم جديد'
            ]
        );

    } catch (\Throwable $th) {

    }
   }
/////////////////

    return $this->createResponse($evaluations);
}

    //////////////// update Evaluation
    public function updateEvaluation(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'value'),
            [
                'id' => 'required|exists:evaluations,id',
                'value' => 'required|string|max:1|in:A,B,C,D,E'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $evaluation = Evaluation::find($request->id);
        $evaluation->update([
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

    //////////////// displey Evaluation
    public function displeyEvaluation(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id', 'lecture_id', 'user_id', 'subject_id','section_id'),
            [
                'subject_id' => 'nullable|exists:subjects,id',
                'course_id' => 'nullable|exists:courses,id',
                'lecture_id' => 'nullable|exists:lectures,id',
                'section_id' => 'nullable|exists:sections,id',
                'user_id' => 'nullable|exists:students,user_id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $evaluation = Evaluation::query();
        if($request->user_id){
            $evaluation->where('user_id',$request->user_id);
        }
        if($request->lecture_id){
            $evaluation->where('lecture_id',$request->lecture_id);
        }
        if($request->subject_id){
            $evaluation->where('subject_id',$request->subject_id);
        }
        if($request->course_id){
            $evaluation->where('course_id',$request->course_id);
        }
        if ($request->section_id) {
            $ids = SectionStudent::where('section_id', $request->section_id)->get()->pluck('user_id');
            $evaluation->whereIn('user_id', $ids);
        }
        return $this->getResponse($evaluation->latest()->paginate($limt));
    }
}
