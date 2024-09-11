<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Lecture;
use App\Models\Subject;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }


    //////// create subjects
    public function createSubject(Request $request)
    {
        $validate = Validator::make(
            $request->only('level_id', 'name', 'logo'),
            [
                'level_id' => 'required|exists:levels,id',
                'name' => 'required|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $image = null;
        if ($request->logo) {
            $image =  $this->setLogo($request,'subject');
        }
        $subject = Subject::create([
            'name' => $request->name,
            'level_id' => $request->level_id,
            'logo' => $image
        ]);
        return $this->createResponse($subject);
    }

    //////// update subjects
    public function updateSubject(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'name', 'logo'),
            [
                'id' => 'required|exists:subjects,id',
                'name' => 'required|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $subject = Subject::find($request->id);
        $image = $subject->logo;
        if ($request->logo) {
            $image =  $this->setLogo($request,'subject');
            if ($subject->image != null) {
                File::delete(public_path($subject->image));
                $subject->image = null;
                $subject->save();
            }
        }
        $subject->update([
            'name' => $request->name,
            'logo' => $image
        ]);
        return $this->updateResponse($subject);
    }

    //////// get subjects
    public function displaySubject(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id', 'level_id'),
            [
                'course_id' => 'exists:courses,id',
                'level_id' => 'exists:levels,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $subjects =  Subject::query();
        if ($request->course_id) {
            $course = Course::find($request->course_id);
            $subjects->where('level_id', $course->level_id);
        }
        if ($request->level_id) {
            $subjects->where('level_id', $request->level_id);
        }
        $subjects->select('id', 'name','logo');
        $arr = $subjects->get()->pluck('name')->unique();
        $keys = array_keys($arr->toArray());
        return $this->getResponse(array_intersect_key($subjects->get()->toArray(), $keys));
    }


    //////// create lecture
    public function createLecture(Request $request)
    {
        $validate = Validator::make(
            $request->only('subject_id', 'name','course_id'),
            [
                'subject_id' => 'required|exists:subjects,id',
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255|unique:lectures,name',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $lecture = Lecture::create([
            'name' => $request->name,
            'subject_id' => $request->subject_id,
            'course_id' => $request->course_id,
        ]);
        $lecture->load('course','subject');
        return $this->createResponse($lecture);
    }

    //////// update lecture
    public function updateLecture(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'subject_id', 'name','course_id'),
            [
                'id' => 'exists:lectures,id',
                'subject_id' => 'exists:subjects,id',
                'course_id' => 'exists:courses,id',
                'name' => 'required|string|max:255|unique:lectures,name,' . $request->id . ''
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $lecture = Lecture::find($request->id);
        $lecture->update([
            'name' => $request->name,
            'subject_id' => $request->subject_id,
            'course_id' => $request->course_id,
        ]);
        $lecture->load('course','subject');
        return $this->updateResponse($lecture);
    }

    //////// display lecture
    public function displayLecture(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('subject_id', 'name','course_id'),
            [
                'subject_id' => 'nullable|exists:subjects,id',
                'course_id' => 'nullable|exists:courses,id',
                'name' => 'nullable|string|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $lecture = Lecture::query()->with('course','subject');
        if($request->subject_id != null){
            $lecture->where('subject_id',$request->subject_id)->with('course','subject');
        }
        if($request->course_id != null){
            $lecture->where('course_id',$request->course_id)->with('course','subject');
        }
        if($request->course_id != null){
            $lecture->where('course_id',$request->course_id);
        }
        if($request->course_id != null){
            $lecture->where('course_id',$request->course_id);
        }
        if($request->course_id != null){
            $lecture->where('course_id',$request->course_id);
        }
        if($request->course_id != null){
            $lecture->where('course_id',$request->course_id);
        }
        if($request->name != null){
            $lecture->where('name','LIKE','%'.$request->name.'%')->with('course','subject');
        }
        return $this->getResponse($lecture->latest()->paginate($limt));
    }

        //////////////// delete lecture
        public function deleteLecture(Request $request)
        {
            $validate = Validator::make(
                $request->only('id'),
                [
                    'id' => 'required|exists:lectures,id',
                ]
            );
            if ($validate->fails()) {
                return $this->badResponse($validate);
            }
            $lecture = Lecture::find($request->id);
            $eva = Evaluation::where('lecture_id',$request->id)->first();
            if($eva){
                return $this->failResponse('لا يمكنك حذف هذا الدرس لوجود تقييمات اخرى مرتبطة به');
            }
            $lecture->delete();
            return $this->deleteResponse($lecture);
        }

            //////// get all subjects
    public function displayAllSubject(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id'),
            [
                'course_id' => 'required|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $subjects =  Subject::query();
        $course = Course::find($request->course_id);
        $subjects->where('level_id', $course->level_id);
        $subjects->select('id', 'name','logo');
        $arr = $subjects->get()->pluck('name')->unique();
        $keys = array_keys($arr->toArray());
        return $this->getResponse(array_intersect_key($subjects->get()->toArray(), $keys));
    }

        //////// display all lecture
        public function displayAllLecture(Request $request)
        {
            $validate = Validator::make(
                $request->only('name'),
                [
                    'name' => 'required|string|max:255',
                ]
            );
            if ($validate->fails()) {
                return $this->badResponse($validate);
            }
            $lecture = Lecture::query();
            $lecture->where('name','LIKE','%'.$request->name.'%');
            $lecture->select('name');
            $arr = $lecture->get()->pluck('name')->unique();
            $keys = array_keys($arr->toArray());
            return $this->getResponse(array_intersect_key($lecture->get()->toArray(), $keys));
        }
}
