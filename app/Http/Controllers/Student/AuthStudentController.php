<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Achievements;
use App\Models\Ads;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\HonoraryBoard;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
class AuthStudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('login','home');
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
        $user = User::where('username', $request->username)->first();
        $section = SectionStudent::where('user_id', $user->id)->get()->pluck('section_id');
        $course_section = Section::whereIn('id', $section)->get()->pluck('course_id');
        $course = Course::whereIn('id', $course_section)->get();
        foreach ($course as $key => $value) {
            $subjects = Subject::where('level_id', $value->level_id)->select('id','logo', 'name')->get();
            $section_id = Section::whereIn('id', $section)->where('course_id', $value->id)->first()->id;
            $value->section_id = $section_id;
            $value->subjects = $subjects;
        }
        $student=Student::where('user_id',$user->id)->with('address')->first();
        $user->profile=$student;
        $user->courses = $course;
        $user->role = $user->roles[0]['name'];
        $user->token=auth()->login($user);
        Arr::forget($user, 'roles');
        return  response()->json([
            'message' => 'login successfully',
            'data' => $user,
        ], 200);
    }


     /// courseStudent
    public function courseStudent()
    {

         $section = SectionStudent::where('user_id', auth()->user()->id)->get()->pluck('section_id');
         $course_section = Section::whereIn('id', $section)->get()->pluck('course_id');
         $course = Course::whereIn('id', $course_section)->get();
        foreach ($course as $key => $value) {
            $subjects = Subject::where('level_id', $value->level_id)->select('id','logo', 'name')->get();
            $section_id = Section::whereIn('id', $section)->where('course_id', $value->id)->first()->id;
            $value->section_id = $section_id;
            $value->subjects = $subjects;
        }
        // return$course;
        return $this->getResponse($course);

    }

    /// profile
    public function profile()
    {
        $student=Student::where('user_id',auth()->user()->id)->with('address')->first();
        $section = SectionStudent::where('user_id', auth()->user()->id)->get()->pluck('section_id');
        $course_section = Section::whereIn('id', $section)->get()->pluck('course_id');
        $course = Course::whereIn('id', $course_section)->get();
        foreach ($course as $key => $value) {
            $subjects = Subject::where('level_id', $value->level_id)->select('id', 'name')->get();
            $section_id = Section::whereIn('id', $section)->where('course_id', $value->id)->first()->id;
            $value->section_id = $section_id;
            $value->subjects = $subjects;
        }
        $student->courses=$course;
        // return$course;
        return $this->getResponse($student);

    }

    /// home
    public function home(Request $request)
    {
        $validate = Validator::make(
            $request->only('level_id'),
            [
                'level_id' => 'nullable|exists:levels,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        if(auth()->user()){
            $section = SectionStudent::where('user_id', auth()->user()->id)->get()->pluck('section_id');
            $course_section = Section::whereIn('id', $section)->get()->pluck('course_id');
            $course = Course::whereIn('id', $course_section)->get();
            foreach ($course as $key => $value) {
                $subjects = Subject::where('level_id', $value->level_id)->select('id','logo', 'name')->get();
                $section_id = Section::whereIn('id', $section)->where('course_id', $value->id)->first()->id;
                $value->section_id = $section_id;
                $value->subjects = $subjects;
            }
        }
        $ads=Ads::where('active',true)->get();
        $teachers=Teacher::select(['id','f_name','l_name','birthday','gender','logo','description'])->get();
        $courses=Course::query();
        if($request->level_id){
            $courses->where('level_id',$request->level_id);
        }
        $courses=$courses->get();
        $ach=Achievements::get();
        $honor=HonoraryBoard::where('study_year','>=',now()->year - 1)->with('student')->get();
        $data=[
            'ads'=>$ads,
            'teachers'=>$teachers,
            'courses'=>$courses,
            'achievements'=>$ach,
            'HonoraryBoard'=>$honor,
            'student_course'=>$course??null,
        ];
        return $this->getResponse($data);

    }

    /// logout
    public function logout()
    {
        auth()->logout();
        return $this->successResponse('logout successfully');
    }
}
