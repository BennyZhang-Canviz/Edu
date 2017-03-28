<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
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
            //return response()->stream()
        }
    }

    /**
     * The education service
     *
     * @var string
     */
    private $educationServiceClient;
}
