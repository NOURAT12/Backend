<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Exam;
use App\Models\Lecture;
use App\Models\Question;
use App\Models\QuestionStudent;
use App\Models\Quiz;
use App\Models\QuizLevel;
use App\Models\QuizStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuizStudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



    //////////////// displey Evaluation
    public function displeyEvaluation(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id','subject_id'),
            [
                'subject_id' => 'nullable|exists:subjects,id',
                'course_id' => 'nullable|exists:courses,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $evaluation = Evaluation::student();
        $user_id = auth()->user()->id;
        $evaluation->where('user_id', $user_id);
        if ($evaluation) {
            if ($request->lecture_id) {
                $evaluation->where('lecture_id', $request->lecture_id);
            }
            if ($request->subject_id) {
                $evaluation->where('subject_id', $request->subject_id);
            }
            if ($request->course_id) {
                $evaluation->where('course_id', $request->course_id);
            }
            return $this->getResponse($evaluation->latest()->paginate($limt));
        }
        return $this->getResponse([]);
    }

    //////////////// displey Exam
    public function displeyExam(Request $request)
    {
        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('course_id', 'subject_id', 'name', 'type'),
            [
                'subject_id' => 'nullable|exists:subjects,id',
                'course_id' => 'nullable|exists:courses,id',
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:255'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user_id = auth()->user()->id;
        $exam = Exam::student()->with('examFiles');
        if ($request->course_id) {
            $exam->where('course_id', $request->course_id);
        }
        if ($request->subject_id) {
            $exam->where('subject_id', $request->subject_id);
        }
        if ($request->type) {
            $exam->where('type', $request->type);
        }
        $exam->whereHas('marks', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
        $exam->with('marks', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
        if ($request->name) {
            $exam->where('name', 'LIKE', '%' . $request->name . '%');
        }
        return $this->getResponse($exam->latest()->paginate($limt));
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
                'type' => 'nullable|string|max:1|in:L,N',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user_id = auth()->user()->id;
        $quiz = Quiz::student()->with('questions.questionStudents');
        $quiz->with('quizStudent', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
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

    //////////////// solve Quiz
    public function solveQuiz(Request $request)
    {

        $validate = Validator::make(
            $request->only('quiz_id', 'question_id', 'solve'),
            [
                'quiz_id' => 'required|exists:quizzes,id',
                'question_id' => 'required|array|min:1',
                'question_id.*' => 'exists:questions,id',
                'solve' => 'required|array|min:1',
                'solve.*' => 'string|max:1|in:A,B,C,D,E',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $user_id = auth()->user()->id;
        if (count($request->solve) != count($request->question_id)) {
            return $this->failResponse('يجب ان يكون حجم المصفوفتين متساويين');
        }

        $quiz = Quiz::find($request->quiz_id);
        $questions = Question::where('quiz_id', $quiz->id)->get();
        $number_qus = count($questions);
        $true_qus = 0;

        if (!$quiz->status['available']) {
            return $this->failResponse($quiz->status['message']);
        }

        DB::beginTransaction();
        try {

            $check = QuizStudent::where('user_id', $user_id)->where('quiz_id', $quiz->id)->first();

            if ($check) {
                $check->delete();
                QuestionStudent::where('user_id', $user_id)->whereIn('question_id', $questions->pluck('id'))->delete();
            }

            for ($i = 0; $i < count($request->solve); $i++) {
                $question = Question::find($request->question_id[$i]);
                if ($question->solve == $request->solve[$i]) {
                    $true_qus += 1;
                }
                QuestionStudent::create([
                    'question_id' => $request->question_id[$i],
                    'user_id' => $user_id,
                    'solve' => $request->solve[$i]
                ]);
            }

            $mark = intval(($true_qus / $number_qus) * 100);

            $solve = QuizStudent::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user_id,
                'state' => $mark > 49 ? 'P' : 'N',
                'question_true_number' => $true_qus,
                'mark' => $mark
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->failResponse($th->getMessage());
        }

        return $this->createResponse($solve);
    }
}
