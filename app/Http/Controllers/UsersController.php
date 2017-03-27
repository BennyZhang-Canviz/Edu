<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
use App\Services\MapService;
use App\Services\TokenCacheServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Services\AADGraphClient;

class UsersController extends Controller
{
    /**
     * Create a new schools controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param string $objectId the object id of the school
     * @param string $schoolId the id of the school
     *
     * @return \Illuminate\Http\Response
     */
    public function index($objectId, $schoolId)
    {
        $user = Auth::user();
        if (!$user->isLinked()){
            return redirect('/link');
        }

        $graphClient = new AADGraphClient();
        $school = $graphClient->getSchool($objectId);
        $users = $graphClient->getUsers($objectId);
        $students = $graphClient->getStudents($schoolId);
        $teachers = $graphClient->getTeachers($schoolId);
        $data = ["school" => $school, "users" => $users, "students" => $students, "teachers" => $teachers];

        return view('users', $data);
    }
}
