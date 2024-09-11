<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Level;
use App\Models\Subject;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class LevelController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

    //////////////// create Level
    public function createLevel(Request $request)
    {
        $validate = Validator::make(
            $request->only('name', 'logo', 'subjects','subjects_logo'),
            [
                'name' => 'required|string|max:250|unique:levels,name',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
                'subjects' => 'required|array|min:1',
                'subjects.*' => 'string|max:200',
                'subjects_logo' => 'required|array|min:1',
                'subjects_logo.*' => 'nullable|image|mimes:jpeg,jpg,png|max:10000',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $subjects = $request->subjects;
        $subjects_logo = $request->subjects_logo;
        if(count($subjects)!=count($subjects_logo)){
            return $this->failResponse("يجب ان يكون حجم المصفوفتين متساويتين");
        }
        $image = null;
        if ($request->logo) {
            $image =  $this->setLogo($request,'level');
        }
        $logos=[];
        for ($i=0; $i <count($subjects_logo) ; $i++) {
            if ($subjects_logo[$i]) {
                $logos[] =  $this->setSubjectLogo($subjects_logo[$i],'subject');
            }else{
                $logos[] = null;
            }
        }
        DB::beginTransaction();
        try {
            $course = Level::create([
                'name' => $request->name,
                'logo' => $image
            ]);
            for ($i=0; $i <count($subjects) ; $i++) {
                Subject::create([
                    'name' => $subjects[$i],
                    'level_id' => $course->id,
                    'logo' => $logos[$i],
                ]);
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->serverResponse();
        }
        $course->subjects;
        $course->courses;
        return $this->createResponse($course);
    }

    //////////////// update Level
    public function updateLevel(Request $request)
    {
        $validate = Validator::make(
            $request->only('id', 'name', 'logo'),
            [
                'id' => 'required|exists:levels,id',
                'name' => 'required|string|max:250|unique:levels,name,' . $request->id . '',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png|max:10000'
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $level = Level::find($request->id);
        $image = $level->logo;
        if ($request->logo) {
            $image =  $this->setLogo($request,'level');
            if ($level->image != null) {
                File::delete(public_path($level->image));
                $level->image = null;
                $level->save();
            }
        }
        DB::beginTransaction();
        try {
            $level->update([
                'name' => $request->name,
                'logo' => $image
            ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->serverResponse();
        }
        $level->subjects;
        $level->courses;
        return $this->updateResponse($level);
    }

    //////////////// delete Level
    public function deleteLevel(Request $request)
    {
        $validate = Validator::make(
            $request->only('id'),
            [
                'id' => 'required|exists:levels,id'

            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $level = Level::find($request->id);
         if($level->course_number>0){
            return $this->failResponse("لا يمكنك حذف هذا المستوى التعليمي لوجود كورسات مرتبطة به");
         }
        if ($level->image != null) {
            File::delete(public_path($level->image));
            $level->image = null;
            $level->save();
        }
        $level->delete();

        return $this->deleteResponse($level);
    }

    //////////////// display Level
    public function displayLevel(Request $request)
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
        $level = Level::query()->with('subjects','courses');
        if ($request->name) {
            $level->where('name', 'like', '%' . $request->name . '%');
        }

        return $this->getResponse($level->get());
    }
    //////////////// check Level Name
    public function checkLevelName(Request $request)
    {

        $limt = $request->limt ? $request->limt : 10;
        $validate = Validator::make(
            $request->only('name'),
            [
                'name' => 'string|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $level = Level::query();
        if ($request->name) {
            $level->where('name', 'like', '%' . $request->name . '%');
        }

        return $this->getResponse($level->get("name"));
    }


    //////////////// display Pair Level
    public function displayPairLevel(Request $request)
    {
        $level = Level::query();
        return $this->getResponse($level->get(['id','name']));
    }
}
