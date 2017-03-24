<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
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
        if (!$user->isLinked()){
            return redirect('/link');
        }

        $graphClient = new AADGraphClient();
        $me = $graphClient->getMe();
        $schools = $graphClient->getSchools();
        $data = ["me" => $me, "schools" => $schools];

        return view('schools', $data);
    }
}
