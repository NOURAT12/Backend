<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Level;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }
 //////////////// create Quiz
 public function createQuiz(Request $request)
 {
     $validate = Validator::make(
         $request->only('name', 'course_id', 'total_time', 'question_number', 'type', 'start', 'subject_id'),
         [
             'name' => 'required|string',
             'course_id' => 'required|exists:courses,id',
             'subject_id' => 'required|exists:subjects,id',
             'total_time' => 'required|date_format:H:i:s',
             'question_number' => 'required|numeric',
             'start' => 'nullable|required_if:type,L|prohibited_if:type,N|date_format:Y-m-d H:i:s|after:'.now()->subMinutes(2),
             'type' => 'required|string|max:1|in:L,N',

         ]
     );
     if ($validate->fails()) {
         return $this->badResponse($validate);
     }
     $course = Course::find($request->course_id);
     $level = Level::find($course->level_id);
     $quiz = Quiz::create([
         'name' => $request->name,
         'total_time' => $request->total_time,
         'question_number' => $request->question_number,
         'type' => $request->type,
         'level_id' => $level->id,
         'subject_id' => $request->subject_id,
         'start' => $request->start,
         'created_by' =>auth()->user()->id,
     ]);
     $quiz_level = QuizLevel::create([
         'quiz_id' => $quiz->id,
         'course_id' => $request->course_id,
     ]);
      ////////////NodeRel
     try {

         $response = Http::get(
            'http://'.'192.168.56.10'.':3000/send',
            [
                 'level_id' => $level->id,
                 'content'=>'تم اضافة ملف اختبار جديد'
             ]
         );

     } catch (\Throwable $th) {

     }
/////////////
     return $this->createResponse($quiz);
 }

    //////////////// update Quiz
    public function updateQuiz(Request $request)
    {
        $validate = Validator::make(
            $request->only('name', 'course_id', 'id', 'total_time', 'question_number', 'type', 'start', 'subject_id'),
            [
                'name' => 'required|string',
                'id' => 'required|exists:quizzes,id',
                'course_id' => 'required|exists:courses,id',
                'subject_id' => 'required|exists:subjects,id',
                'total_time' => 'required|date_format:H:i',
                'question_number' => 'required|numeric',
                'start' => 'required_if:type,L|date',
                'type' => 'required|string|max:1|in:L,N'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $quiz = Quiz::find($request->id);
        $quiz->update([
            'name' => $request->name,
            'total_time' => $request->total_time,
            'question_number' => $request->question_number,
            'type' => $request->type,
            'course_id' => $request->course_id,
            'subject_id' => $request->subject_id,
            'start' => $request->start
        ]);

        return $this->updateResponse($quiz);
    }

    //////////////// delete Quiz
    public function deleteQuiz(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:quizzes,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $quiz = Quiz::find($request->id);
        $quiz->delete();
        return $this->deleteResponse($quiz);
    }

    //////////////// set Quiz to course
    public function setQuizToCourse(Request $request)
    {
        $validate = Validator::make(
            $request->only('id','course_id'),
            [
                'id' => 'required|exists:quizzes,id',
                'course_id' => 'required|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $quiz = Quiz::find($request->id);
        $course = Course::find($request->course_id);
        if($quiz->level_id!=$course->level_id){
            return $this->t403Response('لا يمكن اضافة هذا الاختبار للكورس المحدد لانهم من مستويين مختلفين');
        }
        $exist= QuizLevel::where('quiz_id',$quiz->id)->where('course_id',$course->id)->first();
        if($exist){
            return $this->failResponse('هذا الاختبار موجود بالفعل في هذا الكورس !!');
        }else{
            $exist= QuizLevel::create([
                'quiz_id' => $quiz->id,
                'course_id' => $course->id
            ]);
        }
        return $this->createResponse($exist);
    }

    //////////////// display Quiz
    public function displayQuiz(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id', 'subject_id', 'type'),
            [
                'course_id' => 'nullable|exists:courses,id',
                'subject_id' => 'nullable|exists:subjects,id',
                'type' => 'nullable|string|max:1',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $quiz = Quiz::query()->with('questions');
        if ($request->course_id) {
            $ids = QuizLevel::where('course_id', $request->course_id)->get()->pluck('quiz_id');
            $quiz->whereIn('id', $ids);
        }
        if ($request->subject_id) {
            $quiz->where('subject_id', $request->subject_id);
        }
        if ($request->type) {
            $quiz->where('type', $request->type);
        }

        return $this->getResponse($quiz->latest()->paginate($limt));
    }

    //////////////// create Question
    public function createQuestion(Request $request)
    {
        $validate = Validator::make(
            $request->only('title', 'image', 'A', 'B', 'C', 'D', 'E', 'solve', 'quiz_id'),
            [
                'title' => 'required|string',
                'A' => 'required|string',
                'B' => 'required|string',
                'C' => 'required_if:solve,C|string',
                'D' => 'required_if:solve,D|string',
                'E' => 'required_if:solve,E|string',
                'solve' => 'required|in:A,B,C,D,E',
                'image' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
                'quiz_id' => 'required|exists:quizzes,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $image = null;
        if ($request->image) {
            $image =  $this->setFile($request->file('image'), 'QuizQuestion');
        }
        $question = Question::create([
            'title' => $request->title,
            'A' => $request->A,
            'B' => $request->B,
            'C' => $request->C,
            'D' => $request->D,
            'E' => $request->E,
            'solve' => $request->solve,
            'image' => $image,
            'quiz_id' => $request->quiz_id,
        ]);

        return $this->createResponse($question);
    }

    //////////////// update Question
    public function updateQuestion(Request $request)
    {
        $validate = Validator::make(
            $request->only('title', 'image', 'A', 'B', 'C', 'D', 'E', 'solve', 'id'),
            [
                'id' => 'required|exists:questions,id',
                'title' => 'required|string',
                'A' => 'required|string',
                'B' => 'required|string',
                'C' => 'nullable|string',
                'D' => 'nullable|string',
                'E' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
                'solve' => 'required',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $question = Question::find($request->id);
        $image = $question->image;
        if ($request->image) {
            $image =  $this->setFile($request->file('image'), 'QuizQuestion');
            if ($question->image != null) {
                File::delete(public_path($question->image));
            }
        }
        $question->update([
            'title' => $request->title,
            'A' => $request->A,
            'B' => $request->B,
            'C' => $request->C,
            'D' => $request->D,
            'E' => $request->E,
            'image' => $image,
            'solve' => $request->solve
        ]);

        return $this->updateResponse($question);
    }

    //////////////// delete Question
    public function deleteQuestion(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:questions,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $question = Question::find($request->id);
        if ($question->image != null) {
            File::delete(public_path($question->image));
        }
        $question->delete();
        return $this->deleteResponse($question);
    }

    //////////////// display Question
    public function displayQuestion(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('quiz_id', 'subject_id'),
            [
                'quiz_id' => 'exists:quizzes,id',
                'subject_id' => 'exists:subjects,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $question = Question::query();
        if ($request->quiz_id) {
            $question->where('quiz_id', $request->quiz_id)->get();
        }
        if ($request->subject_id) {
            $ids = Quiz::where('subject_id', $request->subject_id)->get()->pluck('id');
            $question->whereIn('quiz_id', $ids);
        }

        return $this->getResponse($question->latest()->paginate($limt));
    }
}
