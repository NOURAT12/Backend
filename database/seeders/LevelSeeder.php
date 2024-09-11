<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sience=['الفيزياء','الكيمياء','التربية الوطنية','التربية الدينية','اللغة الإنكليزية','اللغة الفرنسية','اللغة العربية','علم الأحياء','الجبر','الأشعة','التحليل'];
        $author=['فلسفة','الجغرافية','التربية الوطنية','التربية الدينية','اللغة الإنكليزية','اللغة الفرنسية','اللغة العربية','التاريخ'];
        $nighn=['الجغرافية','التربية الوطنية','التربية الدينية','اللغة الإنكليزية','اللغة الفرنسية','اللغة العربية','التاريخ','الفيزياء','الكيمياء','علم الأحياء'];
        $tenth=['الفيزياء','الكيمياء','التربية الوطنية','التربية الدينية','اللغة الإنكليزية','اللغة الفرنسية','اللغة العربية','علم الأحياء','الجبر','الأشعة','التحليل'];
        $Eleventh=['الفيزياء','الكيمياء','التربية الوطنية','التربية الدينية','اللغة الإنكليزية','اللغة الفرنسية','اللغة العربية','علم الأحياء','الجبر','الأشعة','التحليل'];

        $level=Level::create([
            'name'=>'بكالوريا علمي'
        ]);
        foreach ($sience as $key => $value) {
            Subject::create([
                'name'=>$value,
                'level_id'=>$level->id
                ]);
        }
        $level=Level::create([
            'name'=>'بكالوريا أدبي'
        ]);
        foreach ($author as $key => $value) {
            Subject::create([
                'name'=>$value,
                'level_id'=>$level->id
                ]);
        }
        $level=Level::create([
            'name'=>'حادي عشر علمي'
        ]);
        foreach ($Eleventh as $key => $value) {
            Subject::create([
                'name'=>$value,
                'level_id'=>$level->id
                ]);
        }
        $level=Level::create([
            'name'=>'عاشر علمي'
        ]);
        foreach ($tenth as $key => $value) {
            Subject::create([
                'name'=>$value,
                'level_id'=>$level->id
                ]);
        }
        $level=Level::create([
            'name'=>'تاسع'
        ]);
        foreach ($nighn as $key => $value) {
            Subject::create([
                'name'=>$value,
                'level_id'=>$level->id
                ]);
        }


    }
}
