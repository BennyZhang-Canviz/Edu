<?php
namespace App\Services;

use App\Config\O365ProductLicenses;
use App\Config\SiteConstants;
use App\ViewModel\SectionUser;
use App\ViewModel\School;
use App\ViewModel\Section;
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
        $user = new SectionUser();
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

    /**
     * Get all schools that exist in the Azure Active Directory tenant
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-all-schools
     * </summary>
     * <returns></returns>
     */
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

    /*
     * <summary>
     * Get a school by using the object_id.
     * Reference URL: https://msdn.microsoft.com/office/office365/api/school-rest-operations#get-a-school.
     *
     * @param string $objectId the object id of the school administrative unit in Azure Active Directory
     *
     * @return The school with the object id
     */
    public function getSchool($objectId)
    {
        return $this->getResponse("get", "/administrativeUnits/" . $objectId . "?api-version=beta", School::class);
    }

    public function getAllMySections($loadMembers)
    {
        $relativeUrl = "/me/memberOf?api-version=1.5";
        $json = $this->getResponse("get", $relativeUrl)["value"];
        $sections = [];
        if (is_array($json) && !empty($json))
        {
            foreach($json as $sec)
            {
                $section = new Section();
                $section->parse($sec);
                if($section->objectType == 'Group' && $section->EducationObjectType =='Section')
                    array_push($sections, $section);
            }
        }


        if(!$loadMembers)
            return $sections;
        $results=[];
        foreach ($sections as $section)
        {
            $sec = $this->getASectionWithMembers($section->objectId);
            array_push($results, $sec);
        }
        return $results;
    }

    private function getASectionWithMembers($sectionId){
        $relativeUrl = '/groups/'.$sectionId.'?api-version=beta&$expand=members';
        $json = $this->getResponse("get", $relativeUrl);
        $section = new Section();
        $section->parse($json);
        $usersArray=[];
        foreach ($section->Users as $user) {
            $u = new EducationUser();
            $u->parse($user);
            array_push($usersArray, $u);
        }
        $section->Users = $usersArray;
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

    public  function getAllSections($schoolId,$top,$nextLink)
    {

        $relativeUrl = '/groups?api-version=beta&$filter=extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType%20eq%20\'Section\'%20and%20extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId%20eq%20\''.$schoolId.'\'';

        $this->HttpGetArrayAsync($relativeUrl,$top,$nextLink);
        $a=1;
        return $a;
    }

    private function HttpGetArrayAsync($relativeUrl,$top,$nextLink)
    {
        $str = strpos($relativeUrl,'?')>=0?'&':'?';
        $relativeUrl =$relativeUrl . $str.'$top='.$top;
        if($nextLink && strpos($nextLink,'?')>=0)
        {
            $token = $this->GetSkipToken($nextLink);
            if($token){
                $relativeUrl =$relativeUrl . "&" .$token;
            }
        }
        $json = $this->getResponse("get", $relativeUrl);
        $a=1;
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
     *
     * @return array members within the school
     */
    public function getMembers($objectId)
    {
        return $this->getResponse("get", "/administrativeUnits/" . $objectId . "members?api-version=beta", SectionUser::class);
    }

    /**
     * Get students within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     *
     * @return array students within the school
     */
    public function getStudents($schoolId)
    {
        return $this->getResponse("get", "users?api-version=1.5&\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '{$schoolId}' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Student'", SectionUser::class);
    }

    /**
     * Get teachers within a school
     * Reference URL: https://msdn.microsoft.com/en-us/office/office365/api/school-rest-operations#get-school-members
     *
     * @param string $schoolId the id of the school administrative unit in Azure Active Directory
     *
     * @return array teachers within the school
     */
    public function getTeachers($schoolId)
    {
        return $this->getResponse("get", "users?api-version=1.5&\$filter=extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId eq '{$schoolId}' and extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType eq 'Teacher'", SectionUser::class);
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