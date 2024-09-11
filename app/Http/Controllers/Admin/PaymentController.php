<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Section;
use App\Models\SectionStudent;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Payment
    public function createPayment(Request $request)
    {
        $validate = Validator::make(
            $request->only('value', 'course_id','user_id'),
            [
                'value' => 'required|string',
                'course_id' => 'required|exists:courses,id',
                'user_id' => 'required|exists:students,user_id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = Payment::create([
            'user_id'=>$request->user_id,
            'value'=>$request->value,
            'course_id' => $request->course_id,
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
        $data=[
        "courses"=>$courses,
        "notes"=>$notes,
        "payments"=>$payments,
        "auth"=>$auth,
        ];

        return $this->createResponse($data);
    }

    //////////////// update Payment
    public function updatePayment(Request $request)
    {
        $validate = Validator::make(
            $request->only('value', 'course_id', 'id','user_id'),
            [
                'id' => 'required|exists:payments,id',
                'value' => 'required|string',
                'course_id' => 'required|exists:courses,id',
                'user_id' => 'required|exists:students,user_id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = Payment::find($request->id);
        $note->update([
            'user_id'=>$request->user_id,
            'value'=>$request->value,
            'course_id' => $request->course_id,
        ]);

        return $this->updateResponse($note);
    }

    //////////////// delete Payment
    public function deletePayment(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:payments,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = Payment::find($request->id);
        $note->delete();
        return $this->deleteResponse($note);
    }

    //////////////// display Payment
    public function displayPayment(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id','user_id'),
            [
                'course_id' => 'exists:courses,id',
                'user_id' => 'exists:students,user_id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $note = Payment::query();
        if ($request->course_id) {
            $note->where('course_id', $request->course_id);
        }
        if ($request->user_id) {
            $note->where('user_id', $request->user_id);
        }

        return $this->getResponse($note->latest()->paginate($limt));
    }
}
