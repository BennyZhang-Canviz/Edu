<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//This is a temp controller and will be merged to schools controller.
class TempSchoolController extends Controller
{
    //
    public function  myclasses($objectId,$schoolId)
    {
        return view('schools.myclasses');
    }
}
