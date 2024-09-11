<?php

use App\Http\Controllers\Admin\AbsenceController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\AttendanceProgram;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EvaluatioController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\FileController as AdminFileController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Student\AuthStudentController;
use App\Http\Controllers\Student\DetailsStudentController;
use App\Http\Controllers\Student\QuizStudentController;
use App\Http\Controllers\Teacher\EvaluationController;
use App\Http\Controllers\Teacher\FileController;
use App\Http\Controllers\Teacher\FunctionController;
use App\Http\Controllers\Teacher\QuizTeacherController;
use App\Models\Ads;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ------------------------- admin ----------------------------------
Route::prefix('admin/')->controller(AuthAdminController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('refresh', 'refresh');
    Route::get('logout', 'logout');
    Route::post('verifyOtp', 'verifyOtp');
    Route::post('sendOtp', 'sendOtp');
    Route::post('resetPassword', 'resetPassword');
});

Route::prefix('admin/')->controller(AccountController::class)->group(function () {
    Route::post('createMasterAccount', 'createMasterAccount');
    Route::post('createTeacherAccount', 'createTeacherAccount');
    Route::post('createStudentAccount', 'createStudentAccount');
    Route::get('displayStudents', 'displayStudents');
    Route::get('displayAllStudents', 'displayAllStudents');
    Route::get('searchByName', 'searchByName');
    Route::get('checkUsername', 'checkUsername');
    Route::get('indexStudent', 'indexStudent');
    Route::get('displayPairTeacher', 'displayPairTeacher');
    Route::get('displayTeachers', 'displayTeachers');
    Route::get('indexTeacher', 'indexTeacher');
    Route::get('searchTeacherByName', 'searchTeacherByName');
});
Route::prefix('admin/')->controller(AddressController::class)->group(function () {
    Route::post('address', 'create');
    Route::put('address', 'update');
    Route::get('address', 'list');
    Route::get('firstOrCreateAddress', 'firstOrCreateAddress');
});
Route::prefix('admin/')->controller(CourseController::class)->group(function () {
    Route::post('course', 'createCourse');
    Route::post('updateCourse', 'updateCourse');
    Route::get('course', 'displayCourse');
    Route::get('displayPairCourse', 'displayPairCourse');
    Route::get('displayCourseTeacher', 'displayCourseTeacher');
    Route::post('setCourseTeacher', 'setCourseTeacher');
    Route::get('checkCourseName', 'checkCourseName');
    Route::delete('deleteCourse', 'deleteCourse');

});

Route::prefix('admin/')->controller(LevelController::class)->group(function () {
    Route::post('level', 'createLevel');
    Route::post('updateLevel', 'updateLevel');
    Route::get('level', 'displayLevel');
    Route::delete('deleteLevel', 'deleteLevel');
    Route::get('checkLevelName', 'checkLevelName');
    Route::get('displayPairLevel', 'displayPairLevel');

});

Route::prefix('admin/')->controller(SectionController::class)->group(function () {
    Route::post('section', 'createSection');
    Route::put('section', 'updateSection');
    Route::get('section', 'displaySection');
    Route::post('setSectionStudent', 'setSectionStudent');
    Route::post('setSectionTeacher', 'setSectionTeacher');
    Route::get('displaySectionStudent', 'displaySectionStudent');
    Route::get('displaySectionTeacher', 'displaySectionTeacher');
    Route::get('displaySectionWithTimeTable', 'displaySectionWithTimeTable');

});

Route::prefix('admin/')->controller(NoteController::class)->group(function () {
    Route::post('noteCourse', 'createNoteCourse');
    Route::put('noteCourse', 'updateNoteCourse');
    Route::delete('noteCourse', 'deleteNoteCourse');
    Route::get('noteCourse', 'displayNoteCourse');
    Route::post('noteStudent', 'createNoteStudent');
    Route::put('noteStudent', 'updateNoteStudent');
    Route::delete('noteStudent', 'deleteNoteStudent');
    Route::get('noteStudent', 'displayNoteStudent');
});

Route::prefix('admin/')->controller(PaymentController::class)->group(function () {
    Route::post('payment', 'createPayment');
    Route::put('payment', 'updatePayment');
    Route::delete('payment', 'deletePayment');
    Route::get('payment', 'displayPayment');

});

Route::prefix('admin/')->controller(AttendanceProgram::class)->group(function () {
    Route::post('programDay', 'createDayProgram');
    Route::put('programDay', 'updateDayProgram');
    Route::delete('programDay', 'deleteDayProgram');
    Route::get('programDay', 'displayProgramSection');
    Route::post('programTime', 'createTimeDayProgram');
    Route::put('programTime', 'updateTimeDayProgram');
    Route::delete('programTime', 'deleteTimeDayProgram');
});

Route::prefix('admin/')->controller(SubjectController::class)->group(function () {
    Route::post('subject', 'createSubject');
    Route::post('updateSubject', 'updateSubject');
    Route::get('subject', 'displaySubject');
    Route::post('lecture', 'createLecture');
    Route::put('lecture', 'updateLecture');
    Route::get('lecture', 'displayLecture');
    Route::delete('lecture', 'deleteLecture');
    Route::get('allLecture', 'displayAllLecture');
    Route::get('allSubject', 'displayAllSubject');

});

Route::prefix('admin/')->controller(EvaluatioController::class)->group(function () {
    Route::post('evaluation', 'createEvaluation');
    Route::put('evaluation', 'updateEvaluation');
    Route::delete('evaluation', 'deleteEvaluation');
    Route::get('evaluation', 'displeyEvaluation');
});

Route::prefix('admin/')->controller(ExamController::class)->group(function () {
    Route::post('exam', 'createExam');
    Route::post('update_exam', 'updateExam');
    Route::delete('exam', 'deleteExam');
    Route::get('exam', 'displeyExam');
    Route::get('allExam', 'displayAllExam');
    Route::get('marksExam', 'displyMarksExam');
    Route::post('setMarkExam', 'setMarkExam');
    Route::post('updateMarkExam', 'updateMarkExam');
});

Route::prefix('admin/')->controller(AbsenceController::class)->group(function () {
    Route::post('absence', 'createAbsence');
    Route::put('absence', 'updateAbsence');
    Route::delete('absence', 'deleteAbsence');
    Route::get('absence', 'displayAbsence');
    Route::get('ads', 'displayAbs');
    Route::get('ach', 'displayAch');

});

Route::prefix('admin/')->controller(QuizController::class)->group(function () {
    Route::post('quiz', 'createQuiz');
    Route::put('quiz', 'updateQuiz');
    Route::delete('quiz', 'deleteQuiz');
    Route::get('quiz', 'displayQuiz');
    Route::post('setQuizToCourse', 'setQuizToCourse');
    Route::post('question', 'createQuestion');
    Route::put('question', 'updateQuestion');
    Route::delete('question', 'deleteQuestion');
    Route::get('question', 'displayQuestion');
});

Route::prefix('admin/')->controller(AdminFileController::class)->group(function () {
    Route::post('file', 'addFile');
    Route::delete('file', 'deleteFile');
    Route::get('file', 'displayFile');
    Route::post('worksheet', 'addWorkSheet');
    Route::delete('worksheet', 'deleteWorkSheet');
    Route::get('worksheet', 'displayWorkSheet');
    Route::post('worksheetSolve', 'addSolveWorkSheet');
    Route::delete('worksheetSolve', 'deleteSolveWorkSheet');
});



// ------------------------- student ----------------------------------
Route::prefix('mobile/')->controller(AuthStudentController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('courseStudent', 'courseStudent');
    Route::get('profile', 'profile');
    Route::get('home', 'home');
    Route::get('logout', 'logout');
});
Route::prefix('mobile/')->controller(QuizStudentController::class)->group(function () {
    Route::get('displeyEvaluation', 'displeyEvaluation');
    Route::get('displeyExam', 'displeyExam');
    Route::get('displayQuiz', 'displayQuiz');
    Route::post('solveQuiz', 'solveQuiz');
});
Route::prefix('mobile/')->controller(DetailsStudentController::class)->group(function () {
    Route::get('displayFile', 'displayFile');
    Route::get('displayNoteCourse', 'displayNoteCourse');
    Route::get('displayProgramSection', 'displayProgramSection');
    Route::get('displayNoteStudent', 'displayNoteStudent');
    Route::get('displayPayment', 'displayPayment');
    Route::get('displayAbsence', 'displayAbsence');
});




// ------------------------- teacher ----------------------------------
Route::prefix('teacher/')->controller(FunctionController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('displayCourseTeacher', 'displayCourseTeacher');
    Route::get('displaySectionTeacher', 'displaySectionTeacher');
    Route::get('displaySectionStudent', 'displaySectionStudent');
    Route::get('displayRandomSectionStudent', 'displayRandomSectionStudent');
});

Route::prefix('teacher/')->controller(EvaluationController::class)->group(function () {
    Route::post('evaluation', 'createEvaluation');
    Route::put('evaluation', 'updateEvaluation');
    Route::delete('evaluation', 'deleteEvaluation');
    Route::get('evaluation', 'displayEvaluation');
});

Route::prefix('teacher/')->controller(QuizTeacherController::class)->group(function () {
    Route::post('quiz', 'createQuiz');
    Route::put('quiz', 'updateQuiz');
    Route::delete('quiz', 'deleteQuiz');
    Route::get('quiz', 'displayQuiz');
    Route::post('setQuizToCourse', 'setQuizToCourse');
    Route::post('question', 'createQuestion');
    Route::put('question', 'updateQuestion');
    Route::delete('question', 'deleteQuestion');
    Route::get('question', 'displayQuestion');
});

Route::prefix('teacher/')->controller(FileController::class)->group(function () {
    Route::post('file', 'addFile');
    Route::delete('file', 'deleteFile');
    Route::get('file', 'displayFile');
    Route::post('worksheet', 'addWorkSheet');
    Route::delete('worksheet', 'deleteWorkSheet');
    Route::get('worksheet', 'displayWorkSheet');
    Route::post('worksheetSolve', 'addSolveWorkSheet');
    Route::delete('worksheetSolve', 'deleteSolveWorkSheet');
});
Route::prefix('teacher/')->controller(SubjectController::class)->group(function () {
    Route::post('lecture', 'createLecture');
    Route::put('lecture', 'updateLecture');
    Route::get('lecture', 'displayLecture');
});
