<?php
namespace App\Services;

use App\Services\TokenCacheServices;
use App\ViewModel\EducationUser;
use App\ViewModel\School;
use App\ViewModel\Student;
use App\ViewModel\Teacher;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Config\SiteConstants;
use App\Config\Roles;
use App\Config\O365ProductLicenses;
use App\Services\UserRolesServices;
use Microsoft\Graph\Connect\Constants;

class AADGraphClient
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheServices();
    }

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

    /**
     * Get the current user.
     *
     * @return Model\User The current user
     */
    public function getMe()
    {
        $json = $this->getResponse("get", "/me?api-version=1.5");
        $assignedLicenses = array_map(function($license){return new Model\AssignedLicense($license);}, $json["assignedLicenses"]);
        $isStudent = $this->IsUserStudent($assignedLicenses);
        $isTeacher = $this->IsUserTeacher($assignedLicenses);
        $user = new EducationUser();
        if ($isStudent)
        {
            $user = new Student();
        }
        else if ($isTeacher)
        {
            $user = new Teacher();
        }
        $user->parse($json);
        return $user;
    }

    public function getSchools()
    {
        $json = $this->getResponse("get", "/administrativeUnits?api-version=beta");
        $value = $json["value"];
        $schools = [];
        if (is_array($value) && !empty($value))
        {
            foreach($value as $json)
            {
                $school = new School();
                $school->parse($json);
                array_push($schools, $school);
            }
        }
        return $schools;
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

        $url = Constants::AADGraph .'/'.$tenantId .'/directoryRoles?api-version=1.6';
        $client = new Client();

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
        $url  =  Constants::AADGraph .'/'.$tenantId.'/directoryRoles/'.$roleId.'/$links/members?api-version=1.6';
        $client = new Client();

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

    /**
     * Get response of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint    The Graph endpoint to call*
     *
     * @return mixed Response of AAD Graph API
     */
    private function getResponse($requestType, $endpoint)
    {
        $token =  $this->getToken();
        if($token)
        {
            $client = new Client();
            $authHeader = $this->getAuthHeader($token);
            $url = Constants::AADGraph . '/' . $_SESSION[SiteConstants::Session_TenantId] . $endpoint;
            $result = $client->request($requestType, $url, $authHeader);
            return json_decode($result->getBody(), true);
        }
        return null;
    }

    /**
     * Get access token
     *
     * @return string The access token
     */
    private function getToken()
    {
        $user = Auth::user();
        if (strlen($user->o365UserId) == 0)
        {
            return null;
        }
        return $this->tokenCacheService-> GetAADToken($user->o365UserId);
    }

    /**
     * Get authorization header for http request
     *
     * @param string $token The access token
     *
     * @return array The authorization header for http request
     */
    private function getAuthHeader($token)
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ];
    }

    private $tokenCacheService;
}