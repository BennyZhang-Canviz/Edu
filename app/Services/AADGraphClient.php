<?php
namespace App\Services;

use App\Services\TokenCacheServices;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Config\SiteConstants;
use App\Config\Roles;
use App\Config\O365ProductLicenses;
use App\Services\UserRolesServices;

class AADGraphClient
{
    //Get current user and roles from AAD. Update user roles to database.
    public function GetCurrentUser($userId)
    {
       $token =  (new TokenCacheServices)->GetMicrosoftToken($userId);
       if($token){
           $graph = new Graph();
           $graph->setAccessToken($token);
           $me = $graph->createRequest("get", "/me")
               ->setReturnType(Model\User::class)
               ->execute();
           $licenses = $graph->createRequest("get", "/me/assignedLicenses")
               ->setReturnType(Model\AssignedLicense::class)
               ->execute();
          $roles=array();
          if($this->IsUserAdmin($userId))
              array_push($roles,Roles::Admin);
          if($this->IsUserStudent($licenses))
              array_push($roles,Roles::Student);
           if($this->IsUserTeacher($licenses))
               array_push($roles,Roles::Faculty);
           (new UserRolesServices)->CreateOrUpdateUserRoles($roles,$userId);

       }

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

    public function GetTenantIdByUserId($userId)
    {
        $tenant = $this->GetTenantByUserId($userId);
        return $this->GetTenantId($tenant);
    }

    private function IsUserAdmin($userId)
    {
        $tenantId='';
        if(isset($_SESSION[SiteConstants::Session_TenantId])){
            $tenantId = $_SESSION[SiteConstants::Session_TenantId];
        }else{
            $tenantId = $this->GetTenantIdByUserId($userId);
        }
        $token =  (new TokenCacheServices)->GetAADToken($userId);
        $adminRoles = $this->GetDirectoryAdminRole($userId,$tenantId,$token);
        $adminMembers = $this->GetAdminDirectoryMembers($tenantId,$adminRoles['value']->objectId,$token);
        $isAdmin=false;
        while ($member = each($adminMembers)) {
          if(stripos($member['value']->url,$userId) != false){
              $isAdmin=true;
            }
        }
        return $isAdmin;
    }

    private function GetDirectoryAdminRole($userId,$tenantId,$token)
    {

        $url = 'https://graph.windows.net/'.$tenantId .'/directoryRoles?api-version=1.6';
        $client = new \GuzzleHttp\Client();

        $result = $client->request('GET', $url, [
            'headers' => [
               'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        $roles = json_decode($result->getBody())->value;
        while ($role = each($roles))
        {
           if( $role['value']->displayName ===SiteConstants::AADCompanyAdminRoleName)
           {
               return $role;
              //$this->GetAdminDirectoryMembers($tenantId,$role['value']->objectId,$token);
           }
        }
    }

    private function GetAdminDirectoryMembers($tenantId,$roleId, $token)
    {
        $url  = 'https://graph.windows.net/'.$tenantId.'/directoryRoles/'.$roleId.'/$links/members?api-version=1.6';
        $client = new \GuzzleHttp\Client();

        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

       return $members = json_decode($result->getBody())->value;
     }

     private function IsUserStudent($licenses)
     {
         while ($license = each($licenses)) {
             if($license['value']->getSkuId() ===O365ProductLicenses::Student || $license['value']->getSkuId() ===O365ProductLicenses::StudentPro){
                 return true;
             }
         }
         return false;
     }
    private function IsUserTeacher($licenses)
    {
        while ($license = each($licenses)) {
            if($license['value']->getSkuId() ===O365ProductLicenses::Faculty || $license['value']->getSkuId() ===O365ProductLicenses::FacultyPro){
                return true;
            }
        }
        return false;
    }
}