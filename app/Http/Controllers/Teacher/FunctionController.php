<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Mail\VerifyMail;
use App\Models\Course;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\CourseTeacher;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\SectionTeacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class FunctionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('login');
    }


    //////// login
    public function login(Request $request)
    {
        $validate = Validator::make(
            $request->only('password', 'username'),
            [
                'password' => 'required|min:8',
                'username' => 'required|string|exists:users,username|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user = User::where('username', $request->username)->first();
        $password = Crypt::decryptString($user->password);
        if ($password != $request->password) {
            return $this->failResponse('البيانات المدخلة غير صحيحة');
        }

            $section = SectionTeacher::where('user_id', $user->id)->get();
            $section_ids = $section->pluck('section_id');
            $course_section = Section::whereIn('id', $section_ids)->get()->pluck('course_id');
            $course = Course::teacher($user->id)->whereIn('id', $course_section)->get();
            $user->courses = $course;
            $teacher = Teacher::where('user_id', $user->id)->first();
            $user->teacher = $teacher;
        $user->role = $user->roles[0]['name'];
        $token = auth()->login($user);
        Arr::forget($user, 'roles');
        return $this->loginResponse($user, $token);
    }

    //////////////// display course Teacher
    public function displayCourseTeacher(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $user=auth()->user();
        $section = SectionTeacher::where('user_id', $user->id)->get();
        $section_ids = $section->pluck('section_id');
        $course_section = Section::whereIn('id', $section_ids)->get()->pluck('course_id');
        $course = Course::teacher($user->id)->whereIn('id', $course_section);
        return $this->getResponse($course->paginate($limt));
    }


    //////////////// display Section Teacher
    public function displaySectionTeacher(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id','subject_id'),
            [
                'course_id' => 'required|exists:courses,id',
                'subject_id' => 'required|exists:subjects,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user = auth()->user();
        $section = SectionTeacher::where('user_id', $user->id)->where('course_id', $request->course_id)->where('subject_id', $request->subject_id)->get();
        $section_ids = $section->pluck('section_id');
        $sections = Section::whereIn('id', $section_ids)->get();
        return $this->getResponse($sections);
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


    //////////////// display Section Student
    public function displayRandomSectionStudent(Request $request)
    {
        $limt = $request->limt ? $request->limt : 5;
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:sections,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $ids = SectionStudent::where('section_id', $request->id)->take('20')->get()->pluck('user_id');
        $students = Student::user()->whereIn('user_id', $ids)->get()->take('20')->random(5);
        return $this->getResponse($students);
    }
}
