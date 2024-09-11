<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\SectionTeacher;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Section
    public function createSection(Request $request)
    {
        $validate = Validator::make(
            $request->only('name', 'course_id'),
            [
                'name' => 'required|string|max:250',
                'course_id' => 'required|exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $course = Course::find($request->course_id);
        $section = Section::firstOrCreate([
            'name' => $request->name,
            'course_id' => $course->id,
            'student_num' => 0,
            'level_id' => $course->level_id
        ]);
        return $this->createResponse($section);
    }


    //////////////// update Section
    public function updateSection(Request $request)
    {
        $validate = Validator::make(
            $request->only('name', 'id'),
            [
                'name' => 'required|string|max:250',
                'id' => 'required|exists:sections,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $section = Section::find($request->id);
        $section->update([
            'name' => $request->name
        ]);
        return $this->updateResponse($section);
    }


    //////////////// display Section
    public function displaySection(Request $request)
    {
        $validate = Validator::make(
            $request->only('name', 'course_id'),
            [
                'name' => 'string|max:250',
                'course_id' => 'exists:courses,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $section = Section::query();
        $section->with('sectionTeacher');
        if ($request->course_id) {
            $section->where('course_id', $request->course_id);
        }
        if ($request->name) {
            $section->where('name', 'LIKE', '%' . $request->name . '%');
        }
        $section->orderBy('course_id');
        return $this->getResponse($section->latest()->get());
    }


    //////////////// set section Student
    public function setSectionStudent(Request $request)
    {
        $validate = Validator::make(
            $request->only('section_id', 'user_ids'),
            [
                'section_id' => 'required|exists:sections,id',
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'required|exists:students,user_id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = $request->user_ids;
        $num=0;
        DB::beginTransaction();
        try {
            foreach ($ids as $key => $value) {
                $courseStudent = SectionStudent::firstOrCreate([
                    'section_id' => $request->section_id,
                    'user_id' => $value
                ]);
                if($courseStudent->wasRecentlyCreated){
                    $num+=1;
                }
            }
            $section=Section::find($request->section_id);
            $section->student_num+=$num;
            $section->save();
            DB::commit();
        } catch (\Throwable $th) {
           DB::rollBack();
           return $this->serverResponse();
        }


        return $this->createResponse($courseStudent);
    }

    //////////////// set section Teacher
    public function setSectionTeacher(Request $request)
    {
        $validate = Validator::make(
            $request->only('user_id', 'section_ids','subject_id'),
            [
                'user_id' => 'required|exists:teachers,user_id',
                'section_ids' => 'required|array|min:1',
                'section_ids.*' => 'required|exists:sections,id',
                'subject_id' => 'required|exists:subjects,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = $request->section_ids;
        DB::beginTransaction();
        try {
            foreach ($ids as $key => $value) {
                $course_id=Section::find($value)->course_id;
                $courseStudent = SectionTeacher::firstOrCreate([
                    'section_id' => $value,
                    'user_id' => $request->user_id,
                    'subject_id' => $request->subject_id,
                    'course_id' => $course_id
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
           DB::rollBack();
           return $this->serverResponse();
        }


        return $this->createResponse($courseStudent);
    }

    //////////////// display Section Teacher
    public function displaySectionTeacher(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:sections,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = SectionTeacher::where('section_id', $request->id)->get()->pluck('user_id');
        $teachers = Teacher::whereIn('user_id', $ids)->paginate($limt);
        return $this->getResponse($teachers);
    }

    //////////////// display Section Student
    public function displaySectionStudent(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:sections,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = SectionStudent::where('section_id', $request->id)->get()->pluck('user_id');
        $students = Student::user()->whereIn('user_id', $ids)->paginate($limt);
        return $this->getResponse($students);
    }
}
