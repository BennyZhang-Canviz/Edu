<?php

namespace App\Http\Controllers\Admin;

use App\Model\Organizations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\AADGraphClient;
use Illuminate\Support\Facades\Input;
use Microsoft\Graph\Connect\Constants;
use App\Config\SiteConstants;

class AdminController extends Controller
{
    public function index()
    {

        $IsAdminConsented=false;
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        if($tenantId){
          $org =  Organizations::where('tenantId',$tenantId)->first();
          if($org && $org->isAdminConsented){
              $IsAdminConsented=true;
          }
        }
        $arrData = array(
            'IsAdminConsented'=>$IsAdminConsented
        );

        return view('admin.index',$arrData);
    }

    public function consent()
    {
        return view('admin.consent');
     }

    public function AdminConsent()
    {
        $redirectUrl = $_SERVER['APP_URL'] . '/admin/processcode';
        $state = uniqid();
        $_SESSION[SiteConstants::Session_State] =$state;
        $url = Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT.'?response_type=code&client_id='.
               Constants::CLIENT_ID.'&resource='.urlencode(Constants::AADGraph).'&redirect_uri='.urlencode($redirectUrl).
               '&state='.$state.'&prompt='.SiteConstants::AdminConsent;
        header('Location: ' . $url);
        exit();
    }

    public function  ProcessCode()
    {
      $a=  Input::get('code');
        dd($a);
    }
}
