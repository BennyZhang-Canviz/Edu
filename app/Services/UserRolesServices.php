<?php
namespace App\Services;
use App\Model\UserRoles;

class UserRolesServices
{
    public function CreateOrUpdateUserRoles($roles, $userId){
        UserRoles::where('UserId',  $userId)->delete();
        while ($role = each($roles)) {
            $userRole = new   UserRoles();
            $userRole->name = $role['value'];
            $userRole->UserId = $userId;
            $userRole->save();
        }
    }

    public function IsUserAdmin($userId){
       $role = UserRoles::where('UserId',  $userId)->first();
       if($role)
           return true;
        return false;
    }
}