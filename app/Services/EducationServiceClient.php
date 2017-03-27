<?php
namespace App\Services;

use App\Config\O365ProductLicenses;
use App\Config\SiteConstants;
use App\ViewModel\EducationUser;
use App\ViewModel\School;
use App\ViewModel\Student;
use App\ViewModel\Teacher;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class  EducationServiceClient
{
    private $tokenCacheService;
    private $o365UserId;
    private $AADGraphClient;

    /**
     * Create a new instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheServices();
        $this->AADGraphClient = new AADGraphClient();
        $user = Auth::user();
        $this->o365UserId = $user->o365UserId;
    }

    /**
     * Get all schools that exist in the Azure Active Directory tenant
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-all-schools
     *
     * @return array all schools that exist in the Azure Active Directory tenant
     */
    public function getSchools()
    {
        return $this->getResponse("get", "/administrativeUnits?api-version=beta", School::class);
    }

    /**
     * Get the school with the object id.
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-a-school.
     *
     * @param string $objectId he Object ID of the school administrative unit in Azure Active Directory
     *
     * @return The school with the object id
     */
    public function getSchool($objectId)
    {
        return $this->getResponse("get", '/administrativeUnits/'.$objectId.'?api-version=beta', School::class);
    }

    /**
     * Get the current user.
     *
     * @return Model\User The current user
     */
    public function getMe()
    {
        $json = $this->getResponse("get", "/me?api-version=1.5", null);
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

    private function IsUserStudent($licenses)
    {
        return $this->AADGraphClient->IsUserStudent($licenses);
    }

    private function IsUserTeacher($licenses)
    {
        return $this->AADGraphClient->IsUserTeacher($licenses);
    }

    /**
     * Get response of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint    The Graph endpoint to call*
     * @param string $returnType The type of the return object or object of an array
     *
     * @return mixed Response of AAD Graph API
     */
    private function getResponse($requestType, $endpoint, $returnType)
    {
        $token =  $this->getToken();
        if($token)
        {
            $url = Constants::AADGraph . '/' . $_SESSION[SiteConstants::Session_TenantId] . $endpoint;
            $result = HttpService::getHttpResponse($requestType, $token, $url);
            $json = json_decode($result->getBody(), true);
            if ($returnType)
            {
                if (array_key_exists('value', $json))
                {
                    $values = $json['value'];
                    //Check that this is an object array instead of a value called "value"
                    if ($values && is_array($values))
                    {
                        $objArray = array();
                        foreach ($values as $obj)
                        {
                            $targetObj = new $returnType();
                            $targetObj->parse($obj);
                            $objArray[] = $targetObj;
                        }
                        return $objArray;
                    }
                }
                $retObj = new $returnType();
                $retObj->parse($result);
                return $retObj;
            }
            return $json;
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

        if (strlen($this->o365UserId) == 0)
        {
            return null;
        }
        return $this->tokenCacheService-> GetAADToken($this->o365UserId);
    }


}