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
    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheServices();
        $this->AADGraphClient = new AADGraphClient();
        $user = Auth::user();
        $this->o365UserId = $user->o365UserId;
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
     *
     * @return mixed Response of AAD Graph API
     */
    private function getResponse($requestType, $endpoint)
    {
        $token =  $this->getToken();
        if($token)
        {
            $url = Constants::AADGraph . '/' . $_SESSION[SiteConstants::Session_TenantId] . $endpoint;
            $result = HttpService::getHttpResponse($requestType, $token, $url);
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

        if (strlen($this->o365UserId) == 0)
        {
            return null;
        }
        return $this->tokenCacheService-> GetAADToken($this->o365UserId);
    }


}