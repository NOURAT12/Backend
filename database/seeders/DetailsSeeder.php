<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\CourseNote;
use App\Models\Evaluation;
use App\Models\Exam;
use App\Models\Lecture;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\StudentNote;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       for ($i=1; $i < 4; $i++) {
        CourseNote::create([
            'course_id'=>$i,
            'note'=>'يعتذر مدرس اللغة العربية عن جلسة الغد على ان تعوض لاحقا'
           ]);
           CourseNote::create([
            'course_id'=>$i,
            'note'=>'يعتذر مدرس اللغة الفرنسية عن جلسة الغد على ان تعوض لاحقا'
           ]);
           CourseNote::create([
            'course_id'=>$i,
            'note'=>'يرجى الاتزام بتعليمات موجه الصف'
           ]);
       }

       StudentNote::create([
        'course_id'=>1,
        'user_id'=>13,
        'note'=>'يرجى تسديد الرسوم المتبقية في اقرب وقت ممكن'
       ]);
       StudentNote::create([
        'course_id'=>1,
        'user_id'=>13,
        'note'=>'يرجى مراجعة الادارة في اقرب وقت ممكن'
       ]);
       StudentNote::create([
        'course_id'=>1,
        'user_id'=>13,
        'note'=>'يرجى الاتزام بالهدوء خلال الجلسات الدراسية'
       ]);

       Payment::create([
        'course_id'=>1,
        'user_id'=>13,
        'value'=>400000
       ]);
       Payment::create([
        'course_id'=>1,
        'user_id'=>13,
        'value'=>500000
       ]);
       Payment::create([
        'course_id'=>1,
        'user_id'=>13,
        'value'=>1000000
       ]);

       Absence::create([
        'course_id'=>1,
        'user_id'=>13,
        'date'=>now(),
        'type'=>'A',
        'late_time'=>null,
        'cause'=>'حالة صحية',
       ]);
       Absence::create([
        'course_id'=>1,
        'user_id'=>13,
        'date'=>now()->addDay(),
        'type'=>'A',
        'late_time'=>null,
        'cause'=>'غير مبرر',
       ]);
       Absence::create([
        'course_id'=>1,
        'user_id'=>13,
        'date'=>now()->addDays(3),
        'type'=>'L',
        'late_time'=>'1:30:00',
        'cause'=>'بسبب المواصلات',
       ]);
       Lecture::create([
        'name'=>'النواس الثقلي',
        'subject_id'=>1,
        'course_id'=>1,
       ]);
       Lecture::create([
        'name'=>'الكهرطيسية',
        'subject_id'=>1,
        'course_id'=>1,
       ]);

       Evaluation::create([
        'user_id'=>30,
        'course_id'=>1,
        'subject_id'=>2,
        'lecture_id'=>1,
        'created_by'=>2,
        'value'=>'A',
       ]);
       Evaluation::create([
        'user_id'=>31,
        'course_id'=>1,
        'subject_id'=>2,
        'lecture_id'=>2,
        'created_by'=>2,
        'value'=>'E',
       ]);

       Exam::create([
        'name'=>'مذاكرة فصل اول',
        'course_id'=>1,
        'subject_id'=>1,
        'date'=>now(),
        'type'=>'t',
        'min_mark'=>180,
        'mark'=>300,
        'solve'=>null,
        'questions'=>null,
       ]);
       Mark::create([
        'exam_id'=>1,
        'user_id'=>30,
        'mark'=>240
       ]);
       Exam::create([
        'name'=>'امتحان فصل أول',
        'course_id'=>1,
        'subject_id'=>2,
        'date'=>now()->addDay(),
        'type'=>'e',
        'min_mark'=>80,
        'mark'=>200,
        'solve'=>null,
        'questions'=>null,
       ]);
       Mark::create([
        'exam_id'=>2,
        'user_id'=>30,
        'mark'=>70
       ]);

       Quiz::create([
        'name'=>'اختبار في الوحدة الاولى كيمياء',
        'level_id'=>1,
        'subject_id'=>2,
        'start'=>null,
        'total_time'=>'00:10:00',
        'question_number'=>5,
        'created_by'=>2,
        'type'=>'N'
       ]);

       Question::create([
        'quiz_id'=>1,
        'solve'=>'A',
        'E'=>null,
        'D'=>null,
        'C'=>null,
        'B'=>'خطأ',
        'A'=>'صح',
        'image'=>null,
        'title'=>'رمز عنصر الحديد هو Fe'
       ]);
       Question::create([
        'quiz_id'=>1,
        'solve'=>'C',
        'E'=>null,
        'D'=>'ليس أي مما سبق',
        'C'=>'CU',
        'B'=>'AS',
        'A'=>'C',
        'image'=>null,
        'title'=>'ما هو رمز عنصر النحاس'
       ]);
       Question::create([
        'quiz_id'=>1,
        'solve'=>'B',
        'E'=>'ليس أي مما سبق',
        'D'=>'Fe',
        'C'=>'AM',
        'B'=>'AI',
        'A'=>'AC',
        'image'=>null,
        'title'=>'ما هو رمز عنصر الألمينيوم'
       ]);
       Question::create([
        'quiz_id'=>1,
        'solve'=>'A',
        'E'=>'ليس أي مما سبق',
        'D'=>'ذرتين اوكسجين و ذرة هيدروجين',
        'C'=>'ذرتين اوكسجين',
        'B'=>'ذرتين هيدروجين',
        'A'=>'ذرتين هيدروجين و ذرة اوكسجين',
        'image'=>null,
        'title'=>'يتكون الماء من :'
       ]);
       Question::create([
        'quiz_id'=>1,
        'solve'=>'A',
        'E'=>'ليس أي مما سبق',
        'D'=>'O2H',
        'C'=>'H2O',
        'B'=>'ClNa',
        'A'=>'NaCl',
        'image'=>null,
        'title'=>'ملح الطعام هو :'
       ]);
       Quiz::create([
        'name'=>'اختبار في مادة الكيمياء',
        'level_id'=>1,
        'subject_id'=>2,
        'start'=>now()->addDays(2),
        'total_time'=>'00:10:00',
        'question_number'=>5,
        'created_by'=>2,
        'type'=>'L'
       ]);

       Question::create([
        'quiz_id'=>2,
        'solve'=>'A',
        'E'=>null,
        'D'=>null,
        'C'=>null,
        'B'=>'خطأ',
        'A'=>'صح',
        'image'=>null,
        'title'=>'رمز عنصر الحديد هو Fe'
       ]);
       Question::create([
        'quiz_id'=>2,
        'solve'=>'C',
        'E'=>null,
        'D'=>'ليس أي مما سبق',
        'C'=>'CU',
        'B'=>'AS',
        'A'=>'C',
        'image'=>null,
        'title'=>'ما هو رمز عنصر النحاس'
       ]);
       Question::create([
        'quiz_id'=>2,
        'solve'=>'B',
        'E'=>'ليس أي مما سبق',
        'D'=>'Fe',
        'C'=>'AM',
        'B'=>'AI',
        'A'=>'AC',
        'image'=>null,
        'title'=>'ما هو رمز عنصر الألمينيوم'
       ]);
       Question::create([
        'quiz_id'=>2,
        'solve'=>'A',
        'E'=>'ليس أي مما سبق',
        'D'=>'ذرتين اوكسجين و ذرة هيدروجين',
        'C'=>'ذرتين اوكسجين',
        'B'=>'ذرتين هيدروجين',
        'A'=>'ذرتين هيدروجين و ذرة اوكسجين',
        'image'=>null,
        'title'=>'يتكون الماء من :'
       ]);
       Question::create([
        'quiz_id'=>2,
        'solve'=>'A',
        'E'=>'ليس أي مما سبق',
        'D'=>'O2H',
        'C'=>'H2O',
        'B'=>'ClNa',
        'A'=>'NaCl',
        'image'=>null,
        'title'=>'ملح الطعام هو :'
       ]);

    }
}
