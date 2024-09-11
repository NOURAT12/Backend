<?php

namespace App\Repository\Interfaces;


interface GroupRepositoryInterface
{
    public function displayGroupMembers($group_id,$user_id);

    public function displayUserGroup($user_id);

    public function displayOtherGroup($user_id);

    public function leaveGroup($group_id,$user_id);

    public function addMember($group_id,$user_id,$username);

    public function deleteGroup($group_id,$user_id);

    public function createGroup($user_id,$name,$description,$image);

}
