<?php

namespace App\Http\Controllers;

use App\Services\EducationServiceClient;
use Illuminate\Http\Request;
//This is a temp controller and will be merged to schools controller.
class TempSchoolController extends Controller
{
    //
    public function  classes($objectId,$schoolId)
    {
        $educationServiceClient = new EducationServiceClient();
        $me = $educationServiceClient->getMe();
        $school = $educationServiceClient->getSchool($objectId);
        //$myClasses =  $educationServiceClient->getMySectionsOfCurrentSchool($schoolId);
        $allClasses = $educationServiceClient->getAllSections($schoolId,12,null);
        $data = ["me" => $me, "school" => $school];
        return view('schools.classes',$data);
    }
}
