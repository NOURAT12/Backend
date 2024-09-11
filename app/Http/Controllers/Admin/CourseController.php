<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseNote;
use App\Models\CourseTeacher;
use App\Models\Exam;
use App\Models\Section;
use App\Models\SectionTeacher;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Teacher;
use App\Models\TimeTable;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class CourseController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create course
    public function createCourse(Request $request)
    {

        $validate = Validator::make(
            $request->only('fees', 'start_date','end_date', 'study_plan', 'name', 'logo', 'level_id'),
            [
                'fees' => 'required|string|max:50',
                'name' => 'required|string|max:250|unique:courses,name',
                'level_id' => 'required|exists:levels,id',
                'study_plan' => 'required|string|max:250',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
                'end_date' => 'required|string|date',
                'start_date' => 'required|string|date'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        // return $request->sections;
        $image = null;
        if ($request->logo) {
            $image =  $this->setLogo($request,'course');
        }
        $course = Course::create([
            'fees' => $request->fees,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'name' => $request->name,
            'active' => 1,
            'study_plan' => $request->study_plan,
            'level_id' => $request->level_id,
            'logo' => $image
        ]);
        for ($i=0; $i <count($request->sections) ; $i++) {
            $section = Section::firstOrCreate([
                'name' => $request->sections[$i]['name'],
                'course_id' => $course->id,
                'student_num' => 0,
                'level_id' => $course->level_id
            ]);
            for ($j=0; $j <count($request->sections[$i]['subject_ids']) ; $j++) {
                $courseStudent = SectionTeacher::firstOrCreate([
                    'section_id' => $section->id,
                    'user_id' => $request->sections[$i]['teachers_ids'][$j],
                    'subject_id' => $request->sections[$i]['subject_ids'][$j],
                    'course_id' => $course->id
                ]);
            }
        }
        $q = Course::query();
        $q = Course::where('id',$course->id);
        $q->with('sections.sectionTeacher');
        return $this->createResponse($q->first());
    }

    //////////////// update course
    public function updateCourse(Request $request)
    {

        $validate = Validator::make(
            $request->only('fees', 'start_date','end_date', 'study_plan', 'name', 'logo', 'id'),
            [
                'id' => 'required|exists:courses,id',
                'fees' => 'required|string|max:50',
                'name' => 'required|string|max:250|unique:courses,name,' . $request->id . '',
                'study_plan' => 'required|string|max:250',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
                'end_date' => 'required|string|date',
                'start_date' => 'required|string|date'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $course = Course::find($request->id);
        $image = $course->logo;
        if ($request->logo) {
            $image =  $this->setLogo($request,'course');
            if ($course->image != null) {
                File::delete(public_path($course->image));
                $course->image = null;
                $course->save();
            }
        }
        $course->update([
            'fees' => $request->fees,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'study_plan' => $request->study_plan,
            'name' => $request->name,
            'logo' => $image
        ]);
        $course->with(['sections.sectionTeacher']);
        $q = Course::query();
        $q = Course::where('id',$course->id);
        $q->with('sections.sectionTeacher');
        return $this->updateResponse($q->first());
    }

    //////////////// display course
    public function displayCourse(Request $request)
    {
        $validate = Validator::make(
            $request->only('name'),
            [
                'name' => 'string|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $course = Course::query();
        $course->with('sections.sectionTeacher');
        if ($request->name) {
            $course->where('name', 'like', '%' . $request->name . '%');
        }

        return $this->getResponse($course->latest()->get());
    }

     //////////////// delete Course
     public function deleteCourse(Request $request)
     {
         $validate = Validator::make(
             $request->only('id'),
             [
                 'id' => 'required|exists:courses,id'

             ]
         );
         if ($validate->fails()) {
             return $this->badResponse($validate);
         }
         $Course = Course::find($request->id);

          if(!$this->checkCourse($request->id)){
             return $this->failResponse("لا يمكنك حذف هذا المستوى التعليمي لوجود بيانات مرتبطة به");
          }
         if ($Course->image != null) {
             File::delete(public_path($Course->image));
             $Course->image = null;
             $Course->save();
         }
         $Course->delete();

         return $this->deleteResponse($Course);
     }

     //////////////// display Pair course
     public function displayPairCourse(Request $request)
     {
         $course = Course::query();
         $course->where('active',1);
         return $this->getResponse($course->get(['id','name','level_id']));
     }

    //////////////// display course Teacher
    public function displayCourseTeacher(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = CourseTeacher::where('course_id', $request->id)->get();
        $teachers = Teacher::query();
        $teachers->whereIn('user_id', $ids->pluck('user_id'));
        return $this->getResponse($teachers->paginate($limt));
    }

    //////////////// set course Teacher
    public function setCourseTeacher(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id', 'user_id','subject_id'),
            [
                'course_id' => 'required|exists:courses,id',
                'user_id' => 'required|exists:teachers,user_id',
                'subject_id' => 'required|exists:subjects,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $courseTeacher = CourseTeacher::create([
            'course_id' => $request->course_id,
            'user_id' => $request->user_id,
            'subject_id' => $request->subject_id
        ]);
        return $this->createResponse($courseTeacher);
    }

    //////////////// check Course Name
    public function checkCourseName(Request $request)
    {
        $validate = Validator::make(
            $request->only('name'),
            [
                'name' => 'string|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $course = Course::query();
        if ($request->name) {
            $course->where('name', 'like', '%' . $request->name . '%');
        }

        return $this->getResponse($course->get(['name','level_id']));
    }


    public function checkCourse($id)
    {
        if(Exam::where('course_id',$id)->exists()){
          return false;
        }
        if(CourseNote::where('course_id',$id)->exists()){
          return false;
        }
        if(StudentNote::where('course_id',$id)->exists()){
          return false;
        }
        if(TimeTable::where('course_id',$id)->exists()){
          return false;
        }
        return true;
    }
}
