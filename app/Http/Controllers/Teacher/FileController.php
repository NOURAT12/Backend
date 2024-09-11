<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseTeacher;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\WrokSheetSolve;
use App\Models\File;
use App\Models\LevelFile;
use App\Models\Subject;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File as FacadesFile;
class FileController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

      //////////////// add File
      public function addFile(Request $request)
      {
          $validate = Validator::make(
              $request->only('level_id', 'name', 'solve', 'type', 'subject_id', 'file'),
              [
                  'name' => 'required|string|max:255',
                  'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:50000',
                  'solve' => 'nullable|array|min:1',
                  'solve.*' => 'file|mimes:pdf,jpg,jpeg,png|max:50000',
                  'level_id' => 'required|exists:levels,id',
                  'type' => 'required|string|max:1|in:W,F',
                  'subject_id' => 'required|exists:subjects,id'
              ]
          );
          if ($validate->fails()) {
              return $this->badResponse($validate);
          }
          $size = $this->getSizeFile($request->file('file'));
          $solve = null;
          if ($request->type == 'W') {
              $path = $this->setFile($request->file('file'), 'workSheets');
              if ($request->solve) {
                  if (count($request->solve) > 1) {
                      foreach ($request->solve as $key => $value) {
                          $solve[] = $this->setFile($value, 'workSheets');
                      }
                  } else {
                      $solve = $this->setFile($request->solve[0], 'workSheets');
                  }
              }
          } else {
              $path = $this->setFile($request->file('file'), 'files');
          }

          $modelfile = File::create([
              'name' => $request->name,
              'path' => $path,
              'size' => $size,
              'solve' => is_array($solve) ? $solve[0] : $solve,
              'subject_id' => $request->subject_id,
              'level_id' => $request->level_id,
              'created_by' =>auth()->user()->id,
              'type' => $request->type
          ]);
          if (is_array($solve)) {
              if (count($solve) > 1) {
                  for ($i = 1; $i < count($solve); $i++) {
                      WrokSheetSolve::create([
                          'file_id' => $modelfile->id,
                          'type' => 'S',
                          'solve' => $solve[$i]
                      ]);
                  }
              }
          }
          return $this->createResponse($modelfile);
      }

      //////////////// delete File
      public function deleteFile(Request $request)
      {
          $validate = Validator::make(
              $request->only('id'),
              [
                  'id' => 'required|exists:files,id'
              ]
          );
          if ($validate->fails()) {
              return $this->badResponse($validate);
          }
          $file = File::find($request->id);
          FacadesFile::delete(public_path($file->path));
          $files = WrokSheetSolve::where('file_id', $file->id)->get();
          if ($file->solve != null) {
              FacadesFile::delete(public_path($file->solve));
          }
          foreach ($files as $key => $value) {
              FacadesFile::delete(public_path($value));
          }
          $file->delete();
          return $this->deleteResponse($file);
      }

      //////////////// display File
      public function displayFile(Request $request)
      {
          $limt = $request->limt ? $request->limt : 10;
          $validate = Validator::make(
              $request->only('name', 'level_id', 'type', 'subject_id'),
              [
                  'name' => 'nullable|string|max:255',
                  'level_id' => 'nullable|exists:levels,id',
                  'type' => 'nullable|string|max:1|in:W,F',
                  'subject_id' => 'nullable|exists:subjects,id'
              ]
          );
          if ($validate->fails()) {
              return $this->badResponse($validate);
          }
          $file = File::query()->with('worksheetsolves');
          $file->where('created_by',auth()->user()->id);
          if ($request->level_id) {
              $file->where('level_id', $request->level_id);
          }
          if ($request->type) {
              $file->where('type', $request->type);
          }
          if ($request->subject_id) {
              $file->where('subject_id', $request->subject_id);
          }
          if ($request->name) {
              $file->where('name', 'LIKE', '%' . $request->name . '%');
          }
          return $this->getResponse($file->latest()->paginate($limt));
      }

      //////////////// add SolveWorkSheet
      public function addSolveWorkSheet(Request $request)
      {
          $validate = Validator::make(
              $request->only('id', 'solve'),
              [
                  'solve' => 'required|array|min:1',
                  'solve.*' => 'required|mimes:pdf,jpg,jpeg,png|max:10000',
                  'id' => 'required|exists:files,id'
              ]
          );
          if ($validate->fails()) {
              return $this->badResponse($validate);
          }
          $worksheet = File::find($request->id);
          if ($worksheet->solve) {
              return $this->failResponse('الملف المختار له حل سابق ');
          }
          if ($worksheet->type != 'W') {
              return $this->failResponse('يجب ان يكون الملف المختار ورقة عمل ');
          }
          $solve = null;
          if (count($request->solve) > 1) {
              foreach ($request->solve as $key => $value) {
                  $solve[] = $this->setFile($value, 'workSheets');
              }
          } else {
              $solve = $this->setFile($request->solve[0], 'workSheets');
          }
          $worksheet->solve = is_array($solve) ? $solve[0] : $solve;
          $worksheet->save();
          if (is_array($solve)) {
              if (count($solve) > 1) {
                  for ($i = 1; $i < count($solve); $i++) {
                      WrokSheetSolve::create([
                          'file_id' => $worksheet->id,
                          'type' => 'S',
                          'solve' => $solve[$i]
                      ]);
                  }
              }
          }
          return $this->successResponse('solve files added successfully');
      }
}
