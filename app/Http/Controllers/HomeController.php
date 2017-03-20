<?php

namespace App\Http\Controllers;


use App\Model\TokenCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Connect\Constants;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Services\TokenCacheServices;

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
        $token= (new TokenCacheServices)->GetMicrosoftToken($o365UserId);

        if($token){
            $graph = new Graph();
            $graph->setAccessToken($token);
            $me = $graph->createRequest("get", "/me")
                ->setReturnType(Model\User::class)
                ->execute();

            $name = $me->getGivenName();
            dd($name);
        }

        return view('home');
    }
}
