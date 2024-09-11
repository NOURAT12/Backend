<?php

namespace App\Repository\Eloquent;

use App\Models\File;
use App\Models\Group;
use App\Models\GroupFile;
use App\Models\GroupUser;
use App\Models\ReservedFile;
use App\Models\UserGroup;
use App\Repository\Interfaces\GroupRepositoryInterface;
use App\Aspect\ServiceTransactionAspect;
use App\Models\User;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\DB;

class GroupRepository implements GroupRepositoryInterface
{

    public function createGroup($user_id,$name,$description,$image)
    {
        ServiceTransactionAspect::before();
        try {
            $group = Group::create([
                'name' => $name,
                'description' => $description,
                'image' => $image,
                'user_id' => $user_id
            ]);
            GroupUser::create([
                'user_id' => $user_id,
                'group_id' => $group->id,
            ]);
            ServiceTransactionAspect::after();
        } catch (\Throwable $th) {
            ServiceTransactionAspect::exception($th);
         return response()->json(['message' => 'somthing worng retry again ','data'=>null], 500);
        }

        return response()->json(['message' => 'created successfully', 'data' => $group], 200);

    }


    public function deleteGroup($group_id,$user_id)
    {
        $group = Group::find($group_id);
        if ($user_id == $group->user_id) {
            $files=File::where('group_id',$group->id)->get();
            foreach ($files as $file ) {
                if(!$file->status){
                    return response()->json(['message' => 'you can\'t do this, there is locked file','data'=>null], 400);
                }
            }
            if($group->image!='1.png'){
                FacadesFile::delete(public_path($group->image));
            }
            $group->delete();
            return  response()->json(['message'=>'deleted success','data'=>$group], 200);
        }
        return response()->json(['message' => 'you can\'t do this','data'=>null], 400);
    }

    public function addMember($group_id,$user_id,$username)
    {
        $group = Group::find($group_id);
        if ($user_id == $group->user_id) {
            $user=User::where('username',$username)->first();
            if($user->id == $user_id){
                return response()->json(['message' => 'you can\'t do this', 'data' => null], 400);
            }
            $member = GroupUser::where('user_id', $user->id)->where('group_id', $group_id)->first();
            if($member){
                return response()->json(['message' => 'This member already exist', 'data' => null], 400);
            }
            GroupUser::create([
                'group_id' => $group_id,
                'user_id' => $user->id,
            ]);
            return response()->json(['message' => 'added successfully', 'data' => $user], 200);
        }
        return response()->json(['message' => 'you can\'t do this','data'=>null], 400);

    }


    public function leaveGroup($group_id,$user_id)
    {
        $group=Group::find($group_id);
        if($user_id == $group->user_id){
            return  response()->json(['message'=>'you can\'t leave your group','data'=>null], 400);
        }
        $member = GroupUser::where('user_id', $user_id)->where('group_id', $group_id)->first();
        if($member){
            $member->delete();
        }else{
            return  response()->json(['message'=>'you are not inside this group'], 400);
        }
        return  response()->json(['message'=>'leaved sucsess','data'=>$member], 200);
    }

    public function displayOtherGroup($user_id)
    {
        $member=GroupUser::where('user_id',$user_id)->get();
        $ides=[];
        for ($i=0; $i <count($member) ; $i++) {
            $ides[]=$member[$i]->group_id;
        }
        $group=Group::whereIn('id',$ides)->where('user_id','!=',$user_id)->paginate(20);
        return response()->json(['message' => 'selected successfully', 'data' => $group], 200);
    }

    public function displayUserGroup($user_id)
    {
        $group = Group::where('user_id',$user_id)->paginate(20);
        return response()->json(['message' => 'selected successfully', 'data' => $group], 200);
    }

    public function displayGroupMembers($group_id,$user_id)
    {
        $group = Group::find($group_id);
        if($group->user_id != $user_id){
            return  response()->json(['message'=> 'you can\'t see other group\'s members','data'=>null], 400);
        }
        $ids=GroupUser::where('group_id',$group->id)->whereNot('user_id',$user_id)->get();
        $users=User::whereIn('id',collect($ids)->pluck('user_id'))->paginate(20);
        return response()->json(['message' => 'selected successfully', 'data' => $users], 200);
    }
}
