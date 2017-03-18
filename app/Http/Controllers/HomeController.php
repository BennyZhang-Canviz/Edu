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
        $graph = new Graph();
        //$token='AQABAAAAAADRNYRQ3dhRSrm-4K-adpCJr5rP6rOFoNAB4FQys8OvTgQzEHtdY5Pqlsx8T9IPkVhi-EDb4aqsKxsOfENia09JIPMErHGiXFEmb4-_-9x4u7vaa-YtNNwVJB6Z36moeLa-SgIO4Wl5LD0bUzIoIhwIO6jqku-UQbOubZBlc0jq1WgvbT40wJjwUOfqqPVLQllF9lDk-X23lp_E1-uNMt_DeJy96qhlgB_zcxL7faBkvbefjqholbmBIlxurERd2i5mvGTMkIzTyXR_EDg5sR9TOUh_FC5bJPPChukowjAhXkBL8fWSPw-ROyUyqHDQNV4SrNpc4vqhCL2HDxj3D0YZva5BtMSXfp_NW0utoaamWoQ_RbAhJQjJJ17GSQ7_4K1qDzb--HjT1GN5uTip8k2FHLgb-PtDPnNW14GXG0E2nbvJO1AZTDl5XfsVuGxMIMTSH1Gk-3BuHQLsFFBdjxzFHmOEWo70mYzb-8uu_dzylPJOq7JG9Y_mgi3vIny1_ibsxQKV1QbhqRpz4MHIauVUvDAx7lffg0CltBFNA2wsrpF9cwWTTVrUxk2bto6XizUgAA';
$token = '';
        $token = '';
        $token = '';
        $token = 'eyJ0eXAiOiJKV1QiLCJub25jZSI6IkFRQUJBQUFBQUFEUk5ZUlEzZGhSU3JtLTRLLWFkcENKMU8xakh3SUxIUDlGblJGOFR3cGRFYklrV1ZhT0VkM3FNbVhXS001V29GeVpwOEhJc2FHVHFCcWtSZ0wyUXVmVlpvOTZiM0xVQnE3RmliNmxhanFEbFNBQSIsImFsZyI6IlJTMjU2IiwieDV0IjoiYTNRTjBCWlM3czRuTi1CZHJqYkYwWV9MZE1NIiwia2lkIjoiYTNRTjBCWlM3czRuTi1CZHJqYkYwWV9MZE1NIn0.eyJhdWQiOiJodHRwczovL2dyYXBoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC82NDQ0NmI1Yy02ZDg1LTRkMTYtOWZmMi05NGVkZGMwYzI0MzkvIiwiaWF0IjoxNDg5ODA4MjgzLCJuYmYiOjE0ODk4MDgyODMsImV4cCI6MTQ4OTgxMjE4MywiYWNyIjoiMSIsImFpbyI6IkFRQUJBQUVBQUFEUk5ZUlEzZGhSU3JtLTRLLWFkcENKdDl2U0JRUUltaW5iRjJMU2NWeU00ZWItdlpuOTJER3hPY1NVZjRJTFBsYWFORFdQZ1BfYUpsTjdrRnlFSUoyeWFkVThaMXFTcTR5dEZYSVlkbHhLV3REclNGLWNMZ0VtaVZpUTdOXzF6T0FnQUEiLCJhbXIiOlsicHdkIl0sImFwcF9kaXNwbGF5bmFtZSI6IlBIUERldiIsImFwcGlkIjoiZjMxM2RjMWEtNjIzNS00YjFmLTgzMzMtNjdhYmRlNmNjODA1IiwiYXBwaWRhY3IiOiIxIiwiZmFtaWx5X25hbWUiOiJUcml2ZWRpIiwiZ2l2ZW5fbmFtZSI6IkFkbWluIiwiaXBhZGRyIjoiMTA0LjIzNy45MS4yMzIiLCJuYW1lIjoiQWRtaW4iLCJvaWQiOiJmMGRmMDAwZi0zODU1LTQ1ZmEtYWQ1MS0zOGM3NTEwNjk0MzEiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzAwMDA5QkE1Q0YzRiIsInNjcCI6IlVzZXIuUmVhZCIsInN1YiI6IjhfWWFTNjZKZVVnNHJBWDhzRmktOUMwM0VaVGI5QWFNdzRHbFc0UHdiRGciLCJ0aWQiOiI2NDQ0NmI1Yy02ZDg1LTRkMTYtOWZmMi05NGVkZGMwYzI0MzkiLCJ1bmlxdWVfbmFtZSI6ImFkbWluQGNhbnZpekVEVS5vbm1pY3Jvc29mdC5jb20iLCJ1cG4iOiJhZG1pbkBjYW52aXpFRFUub25taWNyb3NvZnQuY29tIiwidmVyIjoiMS4wIiwid2lkcyI6WyI2MmU5MDM5NC02OWY1LTQyMzctOTE5MC0wMTIxNzcxNDVlMTAiXX0.pAFguxqWQB2SpVYODRIUaSgoGqhnQvdP2-_mEd3K8Wce4QrojC7ROf3BLKjjaEgQ8EbVqMvuwGMvCgtRvE60h0Hbc_PRuy28IdamtAoEZlcEzp_dQSc65Otmp7JRFJS13Z6z1s54FM6C_xhvJsOjBvuttwwbSC6NAbQQ0omKt6OFGHUirRF3CVBi_Kw9zMgYMW93DH5qPn4cCEOXg7RwM07A2g_xGrMFhEqhiv05vQyqV58_g4gIiKJPeeQzazoLPU3tEA-7ZmRq0fyyXqWMq_tZziER3f0dpBvZtOmEIrtMuc9wBjW74ANnG6vHHZUjlyNwBqPWJ8lBLCKdYJinxQ';
        if($token){

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
