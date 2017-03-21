<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use App\Services\AADGraphClient;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
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
        $o365UserId = $user->o365UserId;

//$grapp = new AADGraphClient;
//
//        $user = Auth::user();
//        $o365UserId = $user->o365UserId;
//
//       $result =  $grapp->GetTenantByUserId($o365UserId);
        //dd($grapp->GetTenantId($result));

       // $u = (new AADGraphClient)->GetCurrentUser($o365UserId);

        return view('home');
    }


}
