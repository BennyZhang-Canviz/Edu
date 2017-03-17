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
        //get user information.
//        $user = Auth::user();
//        $id = Auth::id();
//        $name = $user->remember_token;
//        dd($user);
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token =(new TokenCacheServices())->GetToken($o365UserId);
        //dd($token);
//        $graph = new Graph();
//        $token='AQABAAAAAADRNYRQ3dhRSrm-4K-adpCJr5rP6rOFoNAB4FQys8OvTgQzEHtdY5Pqlsx8T9IPkVhi-EDb4aqsKxsOfENia09JIPMErHGiXFEmb4-_-9x4u7vaa-YtNNwVJB6Z36moeLa-SgIO4Wl5LD0bUzIoIhwIO6jqku-UQbOubZBlc0jq1WgvbT40wJjwUOfqqPVLQllF9lDk-X23lp_E1-uNMt_DeJy96qhlgB_zcxL7faBkvbefjqholbmBIlxurERd2i5mvGTMkIzTyXR_EDg5sR9TOUh_FC5bJPPChukowjAhXkBL8fWSPw-ROyUyqHDQNV4SrNpc4vqhCL2HDxj3D0YZva5BtMSXfp_NW0utoaamWoQ_RbAhJQjJJ17GSQ7_4K1qDzb--HjT1GN5uTip8k2FHLgb-PtDPnNW14GXG0E2nbvJO1AZTDl5XfsVuGxMIMTSH1Gk-3BuHQLsFFBdjxzFHmOEWo70mYzb-8uu_dzylPJOq7JG9Y_mgi3vIny1_ibsxQKV1QbhqRpz4MHIauVUvDAx7lffg0CltBFNA2wsrpF9cwWTTVrUxk2bto6XizUgAA';
//
//        if($token){
//
//            $graph->setAccessToken($token);
//            $me = $graph->createRequest("get", "/me")
//                ->setReturnType(Model\User::class)
//                ->execute();
//
//            $name = $me->getGivenName();
//            dd($name);
//        }

        return view('home');
    }
}
