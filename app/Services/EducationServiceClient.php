<?php
namespace App\Services;

use \DateTime;
use App\Config\O365ProductLicenses;
use App\Config\Roles;
use App\Config\SiteConstants;
use App\ViewModel\ArrayResult;
use App\ViewModel\SectionUser;
use App\ViewModel\School;
use App\ViewModel\Section;
use App\ViewModel\Student;
use App\ViewModel\Teacher;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Prophecy\Util\StringUtil;

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
     * Get the current user.
     *
     * @return Model\User The current user
     */
    public function getMe()
    {
        $json = $this->getResponse("get", "/me?api-version=1.5", null, null, null);
        $assignedLicenses = array_map(function($license){return new Model\AssignedLicense($license);}, $json["assignedLicenses"]);
        $isStudent = $this->IsUserStudent($assignedLicenses);
        $isTeacher = $this->IsUserTeacher($assignedLicenses);
        $user = new SectionUser();
        if ($isStudent)
        {
            $user = new Student();
            $user->userRole=Roles::Student;
        }
        else if ($isTeacher)
        {
            $user = new Teacher();
            $user->userRole=Roles::Faculty;
        }else{
            $user->userRole=Roles::Admin;
        }
        $user->parse($json);
        return $user;
    }

    /**
     * Get all schools that exist in the Azure Active Directory tenant
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-all-schools
     *
     * @return array all schools that exist in the Azure Active Directory tenant
     */
    public function getSchools()
    {
        return $this->getAllPages("get", "/administrativeUnits?api-version=beta", School::class);
    }

    /**
     *
     * Get a school by using the object_id.
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-a-school.
     *
     * @param string $objectId the object id of the school administrative unit in Azure Active Directory
     *
     * @return The school with the object id
     */
    public function getSchool($objectId)
    {
        return $this->getResponse("get", "/administrativeUnits/" . $objectId . "?api-version=beta", School::class, null, null);
    }

    public function getAllMySections($loadMembers)
    {
        $relativeUrl = "/me/memberOf?api-version=1.5";
        $memberOfs = $this->getAllPages("get", $relativeUrl,Section::class);
        $sections=[];
        if (is_array($memberOfs) && !empty($memberOfs))
        {
            foreach($memberOfs as $sec)
            {
                if($sec->objectType == 'Group' && $sec->EducationObjectType =='Section')
                    array_push($sections, $sec);
            }
        }


        if(!$loadMembers)
            return $sections;
        $results=[];
        foreach ($sections as $section)
        {
            $sec = $this->getSectionWithMembers($section->objectId);
            array_push($results, $sec);
        }
        return $results;
    }

    public function getSectionWithMembers($sectionId){
        $relativeUrl = '/groups/'.$sectionId.'?api-version=beta&$expand=members';
        $section = $this->getResponse("get", $relativeUrl,Section::class,null,null);
        return $section;
    }


    public function getMySectionsOfCurrentSchool($schoolId)
    {
        $sections= $this->getAllMySections(true);
        $result =  array_filter($sections, function ($var)  use ($schoolId){
            return ($var->SchoolId== $schoolId);
        });

        $flag = true;
        $temp=0;
        $count = count($result)-1;

        while ( $flag )
        {
            $flag = false;
            for( $j=0;  $j < $count ; $j++)
            {
                if ( $result[$j]->CombinedCourseNumber() > $result[$j+1]->CombinedCourseNumber() )
                {
                    $temp = $result[$j];
                    $result[$j] = $result[$j+1];
                    $result[$j+1]=$temp;
                    $flag = true;
                }
            }
        }
        return $result;
    }

    public  function getAllSections($schoolId,$top,$token)
    {
        $relativeUrl = '/groups?api-version=beta&$filter=extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType%20eq%20\'Section\'%20and%20extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId%20eq%20\''.$schoolId.'\'';
        return  $this->HttpGetArrayAsync($relativeUrl,$top,$token);
    }

    private function HttpGetArrayAsync($relativeUrl,$top,$token)
    {
       return $json = $this->getResponse("get", $relativeUrl,Section::class,$top,$token);
    }
    private function GetSkipToken($nextLink)
    {
        $pattern = '/\$skiptoken=[^&]+/';
        preg_match($pattern, $nextLink, $match);
        if (count($match) == 0)
            return '';
        return $match[0];
    }
    /**
     * Get members within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $objectId the object id of the school administrative unit in Azure Active Directory
     * @param int $top The number of items to return in a result set.
     * @param int $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array members within the school
     */
    public function getMembers($objectId, $top, $skipToken)
    {
        return $this->getResponse("get", "/administrativeUnits/" . $objectId . "/members?api-version=beta", SectionUser::class, $top, $skipToken);
    }

    /**
     * Get students within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     * @param int $top The number of items to return in a result set.
     * @param int $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array students within the school
     */
    public function getStudents($schoolId, $top, $skipToken)
    {
        return $this->getResponse("get", "/users?api-version=1.5&\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '$schoolId' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Student'", SectionUser::class, $top, $skipToken);
    }

    /**
     * Get teachers within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     * @param int $top The number of items to return in a result set.
     * @param int $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return array teachers within the school
     */
    public function getTeachers($schoolId, $top, $skipToken)
    {
        return $this->getResponse("get", "/users?api-version=1.5&\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '$schoolId' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Teacher'", SectionUser::class, $top, $skipToken);
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
     * @param string $endpoint The Graph endpoint to call
     * @param string $returnType The type of the return object or object of an array
     * @param int $top The number of items to return in a result set.
     * @param int $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return mixed Response of AAD Graph API
     */
    private function getResponse($requestType, $endpoint, $returnType, $top, $skipToken)
    {
        $token =  $this->getToken();
        if($token)
        {
            $url = Constants::AADGraph . '/' . $this->getTenantId() . $endpoint;
            if ($top)
            {
                $url = $this->appendParamToUrl($url, "\$top", $top);
            }
            if ($skipToken)
            {
                $url = $this->appendParamToUrl($url, "\$skiptoken", $skipToken);
            }
            $result = HttpService::getHttpResponse($requestType, $token, $url);
            $json = json_decode($result->getBody(), true);
            if ($returnType)
            {
                $isArray = (array_key_exists('value', $json) and is_array($json['value']));
                $retObj = $isArray ? new ArrayResult($returnType) : new $returnType();
                $retObj->parse($json);
                return $retObj;
            }
            return $json;
        }
        return null;
    }

    /**
     * Get all pages of data of AAD Graph API
     *
     * @param string $requestType The HTTP method to use, e.g. "GET" or "POST"
     * @param string $endpoint    The Graph endpoint to call
     * @param string $returnType  The type of the return object or object of an array
     *
     * @return mixed All pages of data of AAD Graph API
     */
    private function getAllPages($requestType, $endpoint, $returnType)
    {
        $data = $nextPage = $this->getResponse($requestType, $endpoint, $returnType, 100, null);
        while($nextPage->skipToken)
        {
            $nextPage = $this->getResponse("get", "/administrativeUnits?api-version=beta", School::class, 100, $data->skipToken);
            $data->value = array_merge($data->value, $nextPage->value);
        }
        return $data->value;
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

    /**
     * Append a parameter to a url
     *
     * @param string $url The url
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     *
     * @return string The url with the appended parameter
     */
    private function appendParamToUrl($url, $name, $value)
    {
        $str = strrchr($url, '?') === false ? "?" : "&";
        $url .= $str . $name . "=" . $value;
        return $url;
    }

    private function  getTenantId()
    {
       $user = Auth::user();
       $o365UserId = $user->o365UserId;
       return $this->AADGraphClient->GetTenantIdByUserId($o365UserId);
    }


}