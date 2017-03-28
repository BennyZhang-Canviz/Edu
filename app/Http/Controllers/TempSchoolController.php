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
        $myClasses =  $educationServiceClient->getMySectionsOfCurrentSchool($schoolId);
        $allClasses = $educationServiceClient->getAllSections($schoolId,12,null);

        foreach ($allClasses as $class1) {
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
        //moke data
        //$allClasses=[];
//        $myClasses=[];
        $data = ["myClasses" => $myClasses, "allClasses"=>$allClasses,"school" => $school,"me"=>$me];
        return view('schools.classes',$data);
    }
}
