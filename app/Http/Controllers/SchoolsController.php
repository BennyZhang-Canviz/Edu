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

class SchoolsController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();


        $graphClient = new AADGraphClient();
        $me = $graphClient->getMe();
        $schools = $graphClient->getSchools();
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
}
