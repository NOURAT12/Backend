<?php

namespace Database\Seeders;

use App\Models\SectionTeacher;
use App\Models\TableDetails;
use App\Models\TimeTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $days=['الأحد','الاثنين','الثلاثاء','الاربعاء','الخميس'];
        for ($i=1; $i <4; $i++) {
            for ($j=1; $j <5 ; $j++) {
                $ts= SectionTeacher::where('section_id',$j)->get();
                for ($k=0; $k <5; $k++) {
                    if($i==3){
                      $day=  TimeTable::create([
                            'course_id'=>$i,
                            'section_id'=>$i*$i+$j-1,
                            'day'=>$days[$k]
                            ]);
                    }else
                    $day=   TimeTable::create([
                        'course_id'=>$i,
                        'section_id'=>$i==1?$j:$i*$i+$j,
                        'day'=>$days[$k]
                        ]);

                 for ($t=0; $t < 5; $t++) {
                    $x=random_int(0,count($ts)-1);
                    TableDetails::create([
                        'time_table_id'=>$day->id,
                        'subject_id'=>$ts[$x]->subject_id,
                        'user_id'=>$ts[$x]->user_id,
                        'time'=>'0'.$t+1 .':30:00',
                        'duration'=>'1:00'
                    ]);
                 }

                }
            }
        }
    }
}
