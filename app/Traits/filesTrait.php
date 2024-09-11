<?php

namespace App\Traits;

use App\Models\File;

/**
 *
 */
trait FilesTrait
{
    function saveFile($request){
        $file = $request->file('file');
        $path = uniqid().'.'.$file->getClientOriginalExtension();
        $Name = $file->getClientOriginalName();
       $modelfile= File::create([
        'name'=>$Name,
        'path'=>$path,
        'user_id'=>$request->user()->id,
        'group_id'=>$request->id,
        'status'=>1
        ]);
        $path = 'files/' . $path;
        $file->move(public_path('files'), $path);
        return $modelfile;
    }


    function updateFile($request,$modelfile){
        $file = $request->file('file');
        $path = uniqid().'.'.$file->getClientOriginalExtension();
        $modelfile->path=$path;
        $modelfile->save();
        $path = 'files/' . $path;
        $file->move(public_path('files'), $path);
        return $modelfile;
    }


    private function getFile( $file,$id)
    {
        return asset('api/v1/file?id='.$id.'&path='.$file);
    }
}
