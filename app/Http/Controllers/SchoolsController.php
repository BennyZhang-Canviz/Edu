<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
use App\Services\CookieService;
use App\Services\EducationServiceClient;
use App\Services\MapService;
use App\Services\MSGraphClient;
use App\Services\TokenCacheServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Services\AADGraphClient;

class SchoolsController extends Controller
{
    /**
     * Show all the schools.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $educationServiceClient = new EducationServiceClient();
        $me = $educationServiceClient->getMe();
        $schools = $educationServiceClient->getSchools();
        foreach($schools as $school)
        {
            $school->isMySchool = $school->schoolId === $me->schoolId;
            $ll = MapService::getLatitudeAndLongitude($school->state, $school->city, $school->address);
            if ($ll)
            {
                $school->latitude = $ll[0];
                $school->longitude = $ll[1];
            }
        }
        usort($schools, function($a, $b)
        {
            if ($a->isMySchool xor $b->isMySchool)
            {
                return $a->isMySchool ? -1 : 1;
            }
            else
            {
                return strcmp($a->displayName, $b->displayName);
            }
        });

        $cookieServices = new CookieService();
        $cookieServices->SetCookies($me->displayName,$me->mail);

        $data = ["me" => $me, "schools" => $schools, "bingMapKey" => Constants::BINGMAPKEY];

        return view('schools.schools', $data);
    }

    /**
     * Show teachers and students of the specified school
     *
     * @param string $objectId The object id of the school
     *
     * @return \Illuminate\Http\Response
     */
    public function users($objectId)
    {
        $educationServiceClient = new EducationServiceClient();
        $school = $educationServiceClient->getSchool($objectId);
        $users = $educationServiceClient->getMembers($objectId, 12, null);
        $students = $educationServiceClient->getStudents($school->schoolId, 12, null);
        $teachers = $educationServiceClient->getTeachers($school->schoolId, 12, null);
        $data = ["school" => $school, "users" => $users, "students" => $students, "teachers" => $teachers];

        return view('schools.users', $data);
    }

    /**
     * Get users of the specified school
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return mixed The next page of users
     */
    public function usersNext($objectId, $skipToken)
    {
        $educationServiceClient = new EducationServiceClient();
        $users = $educationServiceClient->getMembers($objectId, 12, $skipToken);
        return response()->json($users);
    }

    /**
     * Get students of the specified school.
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return mixed The next page of students
     */
    public function studentsNext($objectId, $skipToken)
    {
        $educationServiceClient = new EducationServiceClient();
        $school = $educationServiceClient->getSchool($objectId);
        $students = $educationServiceClient->getStudents($school->schoolId, 12, $skipToken);
        return response()->json($students);
    }

    /**
     * Get teachers of the specified school.
     *
     * @param string $objectId The object id of the school
     * @param string $skipToken The token used to retrieve the next subset of the requested collection
     *
     * @return mixed The next page of teachers
     */
    public function teachersNext($objectId, $skipToken)
    {
        $educationServiceClient = new EducationServiceClient();
        $school = $educationServiceClient->getSchool($objectId);
        $teachers = $educationServiceClient->getTeachers($school->schoolId, 12, $skipToken);
        return response()->json($teachers);
    }

    /**
     * Get photo of the specified user
     *
     * @param string $o365UserId The Office 365 user id of the user
     *
     * @return \Illuminate\Http\Response
     */
    public function userPhoto($o365UserId)
    {
        $msGraph = new MSGraphClient();
        $stream = $msGraph->getUserPhoto($o365UserId);
        if ($stream)
        {
            $contents = $stream->getContents();
            $headers = [
                "Content-type" => "image/jpeg",
                "Accept-Ranges" => "bytes",
                "Content-Length" => strlen($contents)
            ];
            return response()->stream(function() use($stream, $contents){
                $out = fopen('php://output', 'wb');
                fwrite($out, $contents);
                fclose($out);
            }, 200, $headers);
        }
        else
        {
            return response()->file(realpath("./public/images/header-default.jpg"));
        }
    }

    /**
     * The education service
     *
     * @var string
     */
    private $educationServiceClient;

    /**
     * Display all classes of a school.
     * @param $objectId
     * @param $schoolId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function  classes($objectId,$schoolId)
    {
        $educationServiceClient = new EducationServiceClient();
        $me = $educationServiceClient->getMe();
        $school = $educationServiceClient->getSchool($objectId);
        $myClasses =  $educationServiceClient->getMySectionsOfCurrentSchool($schoolId);
        $allClasses = $educationServiceClient->getAllSections($schoolId,12,null);

        foreach ($allClasses->value as $class1) {
            $class1->IsMySection=false;
            foreach ($myClasses as $class2){
                if($class1->Email == $class2->Email){
                    {
                        $class1->IsMySection=true;
                        $class1->Users = $class2->Users;
                        break;
                    }
                }
            }
        }

        $data = ["myClasses" => $myClasses, "allClasses"=>$allClasses,"school" => $school,"me"=>$me];
        return view('schools.classes',$data);
    }

    /**
     * Show next 12 schools for classes page.
     * @param $schoolId
     * @param $nextLink
     * @return \Illuminate\Http\JsonResponse
     */
    public function classesNext($schoolId,$nextLink)
    {
        $educationServiceClient = new EducationServiceClient();
        $myClasses =  $educationServiceClient->getMySectionsOfCurrentSchool($schoolId);
        $school = $educationServiceClient->getSchool($schoolId);
        $allClasses = $educationServiceClient->getAllSections($school->schoolId,12,$nextLink);
        foreach ($allClasses->value as $class) {
            $class->CombinedCNumber = $class->CombinedCourseNumber();
        }
        return  response()->json(['Sections' => $allClasses,'MySections'=>$myClasses,'School'=>$school]);

    }
}
