<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Master;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////// create Master Account
    public function createMasterAccount(Request $request)
    {
        $validate = Validator::make(
            $request->only('username', 'password', 'address_id', 'gender', 'salary', 'phone', 'birthday', 'l_name', 'f_name'),
            [
                'gender' => 'required|string|max:1|min:1',
                'salary' => 'required|integer',
                'address_id' => 'required|exists:addresses,id',
                'username' => 'string|unique:users,username|max:255',
                'phone' => 'required|string|max:10',
                'birthday' => 'required|date',
                'l_name' => 'required|string|max:255',
                'f_name' => 'required|string|max:255',
                'password' => 'string|min:8',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $password = $this->generatePassword();
        if ($request->password) {
            $password = $request->password;
        }
        $full_name = $request->f_name . '' . $request->l_name;
        if ($request->username) {
            $username = $request->username;
        } else {
            $username = $this->getUsername($full_name);
        }
        $role = Role::where('name', 'master')->first();
        DB::beginTransaction();
        try {
            $user =  User::create([
                'name' => $full_name,
                'username' => $username,
                'active' => 1,
                'otp' => null,
                'password' => Crypt::encryptString($password)
            ]);
            $user->addRole($role);
            $master = Master::create([
                'gender' => $request->gender,
                'salary' => $request->salary,
                'phone' => $request->phone,
                'birthday' => $request->birthday,
                'l_name' => $request->l_name,
                'f_name' => $request->f_name,
                'address_id' => $request->address_id,
                'user_id' => $user->id,
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->serverResponse();
        }

        $master->password = $password;
        $master->username = $username;
        return $this->createResponse($master);
    }

    //////// create Teacher Account
    public function createTeacherAccount(Request $request)
    {
        $validate = Validator::make(
            $request->only('username', 'logo', 'password', 'address_id', 'gender', 'salary', 'phone', 'birthday', 'l_name', 'f_name'),
            [
                'gender' => 'required|string|max:1|min:1',
                'address_id' => 'required|exists:addresses,id',
                'username' => 'string|unique:users,username|max:255',
                'phone' => 'required|string|max:10',
                'birthday' => 'required|date',
                'l_name' => 'required|string|max:255',
                'f_name' => 'required|string|max:255',
                'password' => 'string|min:8',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',

            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $password = $this->generatePassword();
        if ($request->password) {
            $password = $request->password;
        }
        $full_name = $request->f_name . '' . $request->l_name;
        if ($request->username) {
            $username = $request->username;
        } else {
            $username = $this->getUsername($full_name);
        }
        $role = Role::where('name', 'teacher')->first();
        $image = null;
        if ($request->logo) {
            $image =  $this->setLogo($request, 'teacher');
        }
        DB::beginTransaction();
        try {
            $user =  User::create([
                'name' => $full_name,
                'username' => $username,
                'active' => 1,
                'otp' => null,
                'password' => Crypt::encryptString($password)
            ]);
            $user->addRole($role);
            $teacher = Teacher::create([
                'gender' => $request->gender,
                'phone' => $request->phone,
                'birthday' => $request->birthday,
                'l_name' => $request->l_name,
                'f_name' => $request->f_name,
                'address_id' => $request->address_id,
                'user_id' => $user->id,
                'logo' => $image,
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->serverResponse();
        }

        $teacher->password = $password;
        $teacher->username = $username;
        return $this->createResponse($teacher);
    }

    //////// create Student Account
    public function createStudentAccount(Request $request)
    {
        $validate = Validator::make(
            $request->only('username','section_id', 'parent_phone', 'mother_name', 'father_name', 'password', 'address_id', 'gender', 'salary', 'phone', 'birthday', 'l_name', 'f_name'),
            [
                'parent_phone' => 'required|string|max:13',
                'mother_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'gender' => 'required|string|max:1|min:1',
                'address_id' => 'required|exists:addresses,id',
                'username' => 'string|unique:users,username|max:255',
                'phone' => 'required|string|max:10',
                'birthday' => 'required|date',
                'l_name' => 'required|string|max:255',
                'f_name' => 'required|string|max:255',
                'password' => 'string|min:8',
                'section_id' => 'required|exists:sections,id',

            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $password = $this->generatePassword();
        if ($request->password) {
            $password = $request->password;
        }
        $full_name = $request->f_name . '' . $request->l_name;
        if ($request->username) {
            $username = $request->username;
        } else {
            $username = $this->getUsername($full_name);
        }
        $role = Role::where('name', 'student')->first();
        $full_name = $request->f_name . ' ' . $request->father_name . ' ' . $request->l_name;
        DB::beginTransaction();
        try {
            $user =  User::create([
                'name' => $full_name,
                'username' => $username,
                'active' => 1,
                'otp' => null,
                'password' => Crypt::encryptString($password)
            ]);
            $user->addRole($role);
            $student = Student::create([
                'parent_phone' => $request->parent_phone,
                'mother_name' => $request->mother_name,
                'father_name' => $request->father_name,
                'full_name' => $full_name,
                'gender' => $request->gender,
                'salary' => $request->salary,
                'phone' => $request->phone,
                'birthday' => $request->birthday,
                'l_name' => $request->l_name,
                'f_name' => $request->f_name,
                'address_id' => $request->address_id,
                'user_id' => $user->id,
            ]);
            $courseStudent = SectionStudent::firstOrCreate([
                'section_id' => $request->section_id,
                'user_id' => $user->id
            ]);
            if($courseStudent->wasRecentlyCreated){
            $section=Section::find($request->section_id);
            $section->student_num+=1;
            $section->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->serverResponse();
        }
        $student->password = $password;
        $student->username = $username;
        return $this->createResponse($student);
    }


    //////// display Students
    public function displayStudents(Request $request)
    {
        $limt = $request->limt ? $request->limt : 50;
        $validate = Validator::make(
            $request->only('gender', 'course_id', 'section_id', 'name'),
            [
                'name' => 'string|max:255',
                'course_id' => 'exists:courses,id',
                'section_id' => 'exists:sections,id',
                'gender' => 'string|max:1|min:1'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $students = Student::user()->with('address');
        if ($request->section_id == null) {
            if ($request->course_id) {
                $sections = Section::where('course_id', $request->course_id)->get()->pluck('id');
                $ids = SectionStudent::whereIn('section_id', $sections)->get()->pluck('user_id');
                $students->whereIn('user_id', $ids);
            }
        } else {
            $ids = SectionStudent::where('section_id', $request->section_id)->get()->pluck('user_id');
            $students->whereIn('user_id', $ids);
        }
        if ($request->gender) {
            $students->where('gender', $request->gender);
        }
        if ($request->name) {
            $students->where('full_name', 'LIKE', '%' . $request->name . '%');
        }


        return $this->getResponse($students->paginate($limt));
    }
    //////// display All Students
    public function displayAllStudents(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id', 'section_id'),
            [
                'course_id' => 'required|exists:courses,id',
                'section_id' => 'exists:sections,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $students = Student::user();
        if ($request->section_id == null) {
            if ($request->course_id) {
                $sections = Section::where('course_id', $request->course_id)->get()->pluck('id');
                $ids = SectionStudent::whereIn('section_id', $sections)->get()->pluck('user_id');
                $students->whereIn('user_id', $ids)->select('user_id','full_name');
            }
        } else {
            $ids = SectionStudent::where('section_id', $request->section_id)->get()->pluck('user_id');
            $students->whereIn('user_id', $ids)->select('user_id','full_name');
        }

        return $this->getResponse($students->get());
    }

    //////// display searchByName
    public function searchByName(Request $request)
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

        $students = Student::search();
        if ($request->name) {
            $students->where('full_name', 'LIKE', '%' . $request->name . '%');
        }

        return $this->getResponse($students->latest()->get(['full_name']));
     }


     //////// display searchByName
     public function checkUsername(Request $request)
     {
        $validate = Validator::make(
            $request->only('username'),
            [
                'username' => 'string|required|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $students= User::query();
        if($request->username){
          $students->where('username','LIKE','%'.$request->username.'%');
        }

        return $this->getResponse($students->get(['username']));
     }

    //////// display account information
    public function displayAccountInformation(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'exists:users,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        if ($request->id != 1) {
            $user = User::select('username','password')->where('id',$request->id)->first();
            $user->password = Crypt::decryptString($user->password);
            $data = [$user->username ,$user->password];
            return  $this->getResponse($data);
        }
        return $this->failResponse('لا يمكنك القيام بهذه العملية');
    }


     //////// index student
     public function indexStudent(Request $request)
     {
         $validate = Validator::make(
             $request->only('id'),
             [
                 'id' => 'exists:users,id',
             ]
         );
         if ($validate->fails()) {
             return $this->badResponse($validate);
         }
        $user = User::select('username','password')->where('id',$request->id)->first();
        $user->password = Crypt::decryptString($user->password);
        $auth = ['username'=> $user->username ,'password'=>$user->password];
        $sections= SectionStudent::where('user_id',$request->id)->get();
        $sections=Section::whereIn('id',$sections->pluck('section_id'))->get();
        $courses= Course::whereIn('id',$sections->pluck('course_id'))->get();
        $notes=StudentNote::with('course')->where('user_id',$request->id)->get();
        $payments=Payment::with('course')->where('user_id',$request->id)->orderBy('course_id')->latest()->get();
        for ($i=0; $i <count($courses) ; $i++) {
            for ($j=0; $j <count($sections) ; $j++) {
               if($sections[$j]->course_id==$courses[$i]->id){
                $courses[$i]->section=$sections[$j]->name;
               }
            }
        }
        $data=[
        "courses"=>$courses,
        "notes"=>$notes,
        "payments"=>$payments,
        "auth"=>$auth,
        ];
        return $this->getResponse($data);
     }

     //////////////// display Pair Teacher
     public function displayPairTeacher(Request $request)
     {
         $Teacher = Teacher::query();
         return $this->getResponse($Teacher->get(['user_id as id','full_name as name']));
     }


      //////// display Teachers
    public function displayTeachers(Request $request)
    {
        $limt = $request->limt ? $request->limt : 50;
        $validate = Validator::make(
            $request->only('gender', 'course_id', 'section_id', 'name'),
            [
                'name' => 'string|max:255',
                'gender' => 'string|max:1|min:1'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $students = Teacher::user()->with('address');

        if ($request->gender) {
            $students->where('gender', $request->gender);
        }
        if ($request->name) {
            $students->where('full_name', 'LIKE', '%' . $request->name . '%');
        }


        return $this->getResponse($students->paginate($limt));
    }

    //////// index Teacher
    public function indexTeacher(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'exists:users,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
       $user = User::select('username','password')->where('id',$request->id)->first();
       $user->password = Crypt::decryptString($user->password);
       $auth = ['username'=> $user->username ,'password'=>$user->password];

       $data=[
       "auth"=>$auth
       ];
       return $this->getResponse($data);
    }


     //////// display searchTeacherByName
     public function searchTeacherByName(Request $request)
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

         $students = Teacher::query();
         if ($request->name) {
             $students->where('full_name', 'LIKE', '%' . $request->name . '%');
         }

         return $this->getResponse($students->latest()->get(['full_name']));
      }
}
