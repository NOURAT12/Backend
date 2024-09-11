<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamFile;
use App\Models\Mark;
use App\Models\SectionStudent;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Exam
    public function createExam(Request $request)
    {
        $validate = Validator::make(
            $request->only('course_id', 'subject_id', 'name', 'date', 'type', 'min_mark', 'mark', 'solve', 'questions',),
            [
                'subject_id' => 'required|exists:subjects,id',
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255',
                'date' => 'required|date',
                'type' => 'required|string|max:255',
                'min_mark' => 'required|integer',
                'mark' => 'required|integer',
                'solve' => 'nullable|array|min:1',
                'solve.*' => 'mimes:jpeg,jpg,png,pdf|max:10000',
                'questions' => 'nullable|array|min:1',
                'questions.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10000',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $additional_questions = false;
        $additional_solve = false;
        $questions = [];
        $solve = [];
        if ($request->questions != null) {
            if (count($request->questions) > 1) {
                $additional_questions = true;
                foreach ($request->questions as $key => $value) {
                    $questions[] = $this->setFile($value, 'exam');
                }
            } else {
                $questions[] = $this->setFile($request->questions[0], 'exam');
            }
        }
        if ($request->solve != null) {
            if (count($request->solve) > 1) {
                $additional_solve = true;
                foreach ($request->solve as $key => $value) {
                    $solve[] = $this->setFile($value, 'exam');
                }
            } else {
                $solve[] = $this->setFile($request->solve[0], 'exam');
            }
        }
        if (!$additional_questions && !$additional_solve) {
            $exam = Exam::create([
                'course_id' => $request->course_id,
                'name' => $request->name,
                'subject_id' => $request->subject_id,
                'date' => $request->date,
                'type' => $request->type,
                'min_mark' => $request->min_mark,
                'mark' => $request->mark,
                'solve' => $request->solve ? $solve[0] : null,
                'questions' => $request->questions ? $questions[0] : null,
            ]);
        } else {
            DB::beginTransaction();
            try {
                $exam = Exam::create([
                    'course_id' => $request->course_id,
                    'name' => $request->name,
                    'subject_id' => $request->subject_id,
                    'date' => $request->date,
                    'type' => $request->type,
                    'min_mark' => $request->min_mark,
                    'mark' => $request->mark,
                    'solve' => $request->solve ? $solve[0] : null,
                    'questions' => $request->questions ? $questions[0] : null,
                ]);
                for ($i = 1; $i < count($questions); $i++) {
                    if ($questions[$i]) {
                        ExamFile::create([
                            'exam_id' => $exam->id,
                            'type' => 'q',
                            'file' => $questions[$i]
                        ]);
                    }
                }
                for ($i = 1; $i < count($solve); $i++) {
                    if ($solve[$i]) {
                        ExamFile::create([
                            'exam_id' => $exam->id,
                            'type' => 's',
                            'file' => $solve[$i]
                        ]);
                    }
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->serverResponse();
            }
        }
        $exam->load('examFiles')->load('course', 'subject');
        return $this->createResponse($exam);
    }


    //////////////// update Exam
    public function updateExam(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'course_id', 'subject_id', 'name', 'date', 'type', 'min_mark', 'mark'),
            [
                'id' => 'required|exists:exams,id',
                'subject_id' => 'required|exists:subjects,id',
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255',
                'date' => 'required|date',
                'type' => 'required|string|max:255',
                'min_mark' => 'required|integer',
                'mark' => 'required|integer'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $exam = Exam::find($request->id);
        $exam->update([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'subject_id' => $request->subject_id,
            'date' => $request->date,
            'type' => $request->type,
            'min_mark' => $request->min_mark,
            'mark' => $request->mark
        ]);
        $exam->load('examFiles')->load('course', 'subject');
        return $this->updateResponse($exam);
    }

    //////////////// delete Exam
    public function deleteExam(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:exams,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }

        $exam = Exam::find($request->id);
        $files = ExamFile::where('exam_id', $exam->id)->get();
        if ($exam->solve != null) {
            File::delete(public_path($exam->solve));
        }
        if ($exam->questions != null) {
            File::delete(public_path($exam->questions));
        }
        foreach ($files as $key => $value) {
            File::delete(public_path($value));
        }
        $exam->delete();

        return $this->deleteResponse($exam);
    }

//////////////// set Mark Exam
public function setMarkExam(Request $request)
{
    $validate = Validator::make(
        $request->only('id', 'marks', 'user_ids'),
        [
            'id' => 'required|exists:exams,id',
            'marks' => 'required|array|min:1',
            'marks.*' => 'required|string|max:6',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:students,user_id',
        ]
    );
    if ($validate->fails()) {
        return $this->badResponse($validate);
    }
    if (count($request->marks) != count($request->user_ids)) {
        return $this->failResponse('يجب ان يكون حجم المصفوفتين متساويين');
    }
    $marks = [];
    for ($i = 0; $i < count($request->marks); $i++) {
        $marks[$i] = Mark::updateOrCreate([
            'exam_id' => $request->id,
            'user_id' => $request->user_ids[$i]
        ],
        [
            'mark' => $request->marks[$i]
        ]);
    }
    ////////////NodeRel
   for ($i=0; $i <count($request->user_ids) ; $i++) {
    try {

        $response = Http::get(
            'http://'.'192.168.56.10'.':3000/send',
            [
                'student_id' => $request->user_ids[$i],
                'content'=>'تم اضافة علامة جديدة'
            ]
        );

    } catch (\Throwable $th) {

    }
   }
 /////////////////

    return $this->createResponse($marks);
}

    //////////////// update Mark Exam
    public function updateMarkExam(Request $request)
    {
        $validate = Validator::make(
            $request->only('mark', 'id'),
            [
                'mark' => 'required|min:1',
                'id' => 'required|exists:marks,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $mark = Mark::find($request->id);
        $mark->update([
            'mark' => $request->mark
        ]);
        return $this->updateResponse($mark);
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

        $exam = Exam::query()->with('examFiles')->with('course', 'subject');
        if ($request->course_id) {
            $exam->where('course_id', $request->course_id);
        }
        if ($request->subject_id) {
            $exam->where('subject_id', $request->subject_id);
        }
        if ($request->type) {
            $exam->where('type', $request->type);
        }
        if ($request->name) {
            $exam->where('name', 'LIKE', '%' . $request->name . '%');
        }
        return $this->getResponse($exam->latest()->paginate($limt));
    }

    //////////////// disply Marks Exam
    public function displyMarksExam(Request $request)
    {
        $validate = Validator::make(
            $request->only('exam_id', 'section_id'),
            [
                'exam_id' => 'required|exists:exams,id',
                'section_id' => 'nullable|exists:sections,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $mark = Mark::query()->where('exam_id', $request->exam_id)->get();
        if ($request->section_id) {
            $ids = SectionStudent::where('section_id', $request->section_id)->get()->pluck('user_id');
            $mark->whereIn('user_id', $ids);
        }
        return $this->getResponse($mark);
    }

    //////// display all exam
    public function displayAllExam(Request $request)
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
        $exam = Exam::query();
        $exam->where('name', 'LIKE', '%' . $request->name . '%');
        $exam->select('name');
        $arr = $exam->get()->pluck('name')->unique();
        $keys = array_keys($arr->toArray());
        return $this->getResponse(array_intersect_key($exam->get()->toArray(), $keys));
    }
}
