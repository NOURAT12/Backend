<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Section;
use App\Models\SectionTeacher;
use App\Models\TableDetails;
use App\Models\TimeTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class AttendanceProgram extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create day programe
    public function createDayProgram(Request $request)
    {

        // $validate = Validator::make(
        //     $request->only('day', 'section_id'),
        //     [
        //         'day' => 'required|string|max:25',
        //         'section_id' => 'required|exists:sections,id',
        //     ]
        // );
        // if ($validate->fails()) {
        //     return $this->badResponse($validate);
        // }
        $days = $request->data;
        for ($i = 0; $i < count($days); $i++) {
            $section = Section::find($days[$i]['section_id']);
            $course = $section->course_id;
            $program = TimeTable::create([
                'day' => $days[$i]['day'],
                'section_id' => $days[$i]['section_id'],
                'course_id' => $course,
            ]);

            $periods = $days[$i]['period'];
            for ($j = 0; $j < count($periods); $j++) {
                $user_id = SectionTeacher::where('section_id', $section->id)->where('subject_id', $periods[$j]['subject_id'])->first();
                $time = TableDetails::create([
                    'time' => $periods[$j]['time'],
                    'time_table_id' => $program->id,
                    'subject_id' => $periods[$j]['subject_id'],
                    'user_id' => $user_id->user_id,
                    'duration' => $periods[$j]['duration'],
                ]);
            }
        }
        return $this->createResponse($program);
    }

    //////////////// update day programe
    public function updateDayProgram(Request $request)
    {

        $validate = Validator::make(
            $request->only('day', 'id'),
            [
                'id' => 'required|exists:time_tables,id',
                'day' => 'required|string|max:25',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $time = TimeTable::find($request->id);
        $time->update([
            'day' => $request->day,
        ]);
        return $this->updateResponse($time);
    }


    //////////////// delete day programe
    public function deleteDayProgram(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:sections,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $time = TimeTable::where('section_id',$request->id)->delete();
        // $time->delete();
        return $this->deleteResponse($time);
    }

    //////////////// display Program section
    public function displayProgramSection(Request $request)
    {

        $limt = $request->limt ? $request->limt : 50;
        $validate = Validator::make(
            $request->only('section_id'),
            [
                'section_id' => 'exists:sections,id',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $days = TimeTable::where('section_id', $request->section_id)->with('table_details');
        return $this->getResponse($days->latest()->paginate($limt));
    }

    //////////////// create time day
    public function createTimeDayProgram(Request $request)
    {
        $validate = Validator::make(
            $request->only('time', 'time_table_id', 'subject_id', 'duration'),
            [
                'time' => 'required',
                'time_table_id' => 'required|exists:time_tables,id',
                'subject_id' => 'required|exists:subjects,id',
                'duration' => 'required|string',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $section = TimeTable::find($request->time_table_id);
        // dd($section->id);
        $user_id = SectionTeacher::where('section_id', $section->section_id)->where('subject_id', $request->subject_id)->first();
        $time = TableDetails::create([
            'time' => $request->time,
            'time_table_id' => $request->time_table_id,
            'subject_id' => $request->subject_id,
            'user_id' => $user_id->user_id,
            'duration' => $request->duration,
        ]);
        return $this->createResponse($time);
    }


    //////////////// update time day
    public function updateTimeDayProgram(Request $request)
    {

        $validate = Validator::make(
            $request->only('time', 'table_details_id', 'subject_id', 'duration'),
            [
                'time' => 'required',
                'table_details_id' => 'required|exists:table_details,id',
                'subject_id' => 'required|exists:subjects,id',
                'duration' => 'required|string',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $table = TableDetails::find($request->table_details_id);
        $table->update([
            'time' => $request->time,
            'subject_id' => $request->subject_id,
            'duration' => $request->duration,
        ]);
        return $this->updateResponse($table);
    }

    //////////////// delete time day

    public function deleteTimeDayProgram(Request $request)
    {

        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:table_details,id'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $table = TableDetails::find($request->id);
        $secId=TimeTable::find($table->time_table_id);
        $table->delete();
        $days = TimeTable::where('section_id', $secId->section_id)->with('table_details');



        return $this->deleteResponse($days->latest()->paginate(50));
    }
}
