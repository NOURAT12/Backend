<?php

namespace App\Repository\Interfaces;


interface FileRepositoryInterface
{
    public function create($request,$user_id,$group_id);

    public function  chickIn($file_ids,$user_id);

    public function chickOut($request,$file_id,$user_id);

    public function report($file_id,$user_id);


    public function delete($file_id,$user_id);

    public function download($file,$user_id);

    public function displayGroupFiles($group_id);

    public function displayUserFiles($user_id);

    public  function saveFile($request);

    public  function updateFile($request,$modelfile);
}
