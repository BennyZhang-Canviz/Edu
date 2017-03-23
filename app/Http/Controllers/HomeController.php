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

        //Below code can get information from Microsoft graph.
//        $user = Auth::user();
//        $o365UserId = $user->o365UserId;
//        $token = (new TokenCacheServices)-> GetMicrosoftToken ($o365UserId);
//        $client = new \GuzzleHttp\Client();
//        $authHeader = [
//            'headers' => [
//                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
//                'Authorization' => 'Bearer ' . $token
//            ]
//        ];
//        $url ='https://graph.microsoft.com/v1.0/me/';
//        $result = $client->request('GET', $url, $authHeader);
//      $body =  $result->getBody();
//
//      $a=json_decode($body);
        return view('home');
    }


}
