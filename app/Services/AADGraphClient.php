<?php
namespace App\Services;

use App\Services\TokenCacheServices;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AADGraphClient
{

    public function GetCurrentUser($userId)
    {
       $token =  (new TokenCacheServices)->GetMicrosoftToken($userId);
       if($token){
           $graph = new Graph();
           $graph->setAccessToken($token);
           $me = $graph->createRequest("get", "/me")
               ->setReturnType(Model\User::class)
               ->execute();
           return $me;
       }
       return null;
    }

    public function GetTenantByUserId($userId)
    {
        $token =  (new TokenCacheServices)->GetMicrosoftToken($userId);
        return $this->GetTenantByToken($token);
     }

    public function GetTenantByToken($token)
    {
        if($token){
            $graph = new Graph();
            $graph->setAccessToken($token);
            $org = $graph->createRequest("get", "/organization")
                ->setReturnType(Model\Organization::class)
                ->execute();
            return $org;
        }
        return null;
    }

    public function GetTenantId($tenant)
    {
        $array  =json_decode( json_encode($tenant[0]));
        return $array->id;
    }
}