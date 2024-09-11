<?php

namespace App\Repository\Eloquent;

use App\Models\File;
use App\Models\User;
use App\Models\FileChick;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\ReservedFile;
use App\Repository\interfaces\FileRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Aspect\ServiceTransactionAspect;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class FileRepository implements FileRepositoryInterface
{
    public function createZip($file)
    {
        $zip = new ZipArchive;
        $subString = substr($file->path, 0, strpos($file->path, '.'));
        $zipFileName = $subString . '.zip';

        if ($zip->open(public_path('files/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
            $zip->addFile(public_path('files/' . $file->path), $file->name);
            $zip->close();

            return $zipFileName;
        } else {
            return false;
        }
    }

    public function create($request, $user_id, $group_id)
    {

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $path = uniqid() . '.' . $extension;
        $Name = $file->getClientOriginalName();
        $sizeInBytes = $file->getSize();
        $size = $sizeInBytes / (1024 * 1024);
        $modelfile = File::create([
            'name' => $Name,
            'path' => $path,
            'user_id' => $user_id,
            'group_id' => $group_id,
            'status' => 1
        ]);
        $path = 'files/' . $path;
        $file->move(public_path('files'), $path);
        $chick=false;
        if($size>=1){
            $chick = $this->createZip($modelfile);
        }
        if($chick){
            FacadesFile::delete(public_path('files/' . $modelfile->path));
            $modelfile->path=$chick;
            $modelfile->save();
        }

        try {
            Http::get(
                'http://192.168.43.220:3000/send'
            );
        } catch (\Throwable $th) {
        }
        return $modelfile;
    }


    public function displayGroupFiles($group_id)
    {
        $files = File::where('group_id', $group_id)->paginate(20);
        return $files;
    }

    public function displayUserFiles($user_id)
    {
        return $files = File::where('user_id', $user_id)->paginate(20);
    }

    public function chickIn($file_ids, $user_id)
    {

        $files = File::whereIn('id', $file_ids)->get();
        $group_id = $files[0]->group_id;
        $group_user = GroupUser::where('group_id', $group_id)->where('user_id', auth()->user()->id)->first();
        if (!$group_user) {
            return response()->json(['message' => 'you are not group member', 'data' => null], 403);
        }
        $check = false;
        $locked = [];
        foreach ($files as $file) {
            if (!$file->status) {
                $check = true;
                $locked[] = $file->id;
            }
        }
        if ($check) {
            return response()->json(['message' => 'there are files locked', 'data' => $locked], 403);
        }
        ServiceTransactionAspect::before();
        try {
            foreach ($files as $file) {
                $file->status = 0;
                $file->save();
                $data = FileChick::create([
                    'status' => 0,
                    'user_id' => $user_id,
                    'file_id' => $file->id
                ]);
            }
            ServiceTransactionAspect::after();
            try {
                Http::get(
                    'http://192.168.43.220:3000/send'
                );
            } catch (\Throwable $th) {
            }
        } catch (\Throwable $th) {
            ServiceTransactionAspect::exception($th);
            return response()->json(['message' => 'somthing worng retry again ', 'data' => null], 500);
        }

        return response()->json(['message' => 'chick in successfully', 'data' => $data], 200);
    }

    public function download($file, $user_id)
    {
        if (!$file->status) {
            $fileChick = FileChick::where('file_id', $file->id)->latest()->first();
            if ($fileChick->user_id == $user_id) {
                return  response()->download(public_path("files/$file->path"), $file->path);
            } else {
                return response()->json(['message' => 'file locked', 'data' => null], 400);
            }
        }
        return  response()->download(public_path("files/$file->path"), $file->path);
    }

    public function delete($file_id, $user_id)
    {

        $file = File::find($file_id);
        if ($user_id == $file->user_id) {
            if (!$file->status) {
                return response()->json(['message' => 'you can\'t do this, the file is locked', 'data' => null], 400);
            }
            FacadesFile::delete(public_path('files/' . $file->path));
            $file->delete();
            return  response()->json(['message' => 'deleted sucsess', 'data' => $file], 200);
        }
        return response()->json(['message' => 'you can\'t do this', 'data' => null], 400);
    }

    public function chickOut($request, $file_id, $user_id)
    {

        $fileChick = FileChick::where('file_id', $file_id)->where('user_id', $user_id)->latest()->first();
        if (!$fileChick) {
            return response()->json(['message' => 'you can\'t do this', 'data' => null], 400);
        }
        $file = File::find($file_id);
        if ($file->status) {
            return response()->json(['message' => 'file is not locked', 'data' => null], 400);
        }
        if ($file->path != null) {
            FacadesFile::delete(public_path('files/' . $file->path));
        }
        $this->updateFile($request, $file);
        $file->status = 1;
        $file->save();
        $data = FileChick::where('file_id', $file_id)->latest()->first();
        $data->status = 1;
        $data->save();
        try {
            Http::get(
                'http://192.168.43.220:3000/send'
            );
        } catch (\Throwable $th) {
        }
        return response()->json(['message' => 'chick out successfully', 'data' => $file], 200);
    }

    public function report($file_id, $user_id)
    {
        $owner = File::where('user_id', $user_id)->where('id', $file_id)->first();
        if (!$owner) {
            return response()->json(['message' => 'you can\'t do this', 'data' => null], 400);
        }
        $files = FileChick::where('file_id', $file_id)->get();
        if (!$files) {
            return response()->json(['message' => 'This file has not been reserve yet', 'data' => null], 400);
        }
        $data = [];
        for ($i = 0; $i < count($files); $i++) {
            $user_id = $files[$i]->user_id;
            $cteate = $files[$i]->created_at;
            $update = $files[$i]->updated_at;
            $user = User::where('id', $user_id)->first();
            $name = $user->username;
            if ($i < count($files) - 1) {
                $data[$i] = "This file is reserved by $name from $cteate to $update";
            }
            if ($i == count($files) - 1) {
                if ($files[$i]->status) {
                    $data[$i] = "This file is reserved by $name from $cteate to $update";
                } else {
                    array_push($data, "it is now reserved by $name");
                }
            }
        }
        return response()->json(['message' => 'fetched successfully', 'data' => $data], 200);
    }




    function saveFile($request)
    {
        $file = $request->file('file');
        $path = uniqid() . '.' . $file->getClientOriginalExtension();
        $Name = $file->getClientOriginalName();
        $modelfile = File::create([
            'name' => $Name,
            'path' => $path,
            'user_id' => $request->user()->id,
            'group_id' => $request->id,
            'status' => 1
        ]);
        $path = 'files/' . $path;
        $file->move(public_path('files'), $path);
        return $modelfile;
    }


    function updateFile($request, $modelfile)
    {
        $file = $request->file('file');
        $path = uniqid() . '.' . $file->getClientOriginalExtension();
        $modelfile->path = $path;
        $modelfile->save();
        $path = 'files/' . $path;
        $file->move(public_path('files'), $path);
        return $modelfile;
    }
}
