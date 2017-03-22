<?php

namespace App\Http\Controllers\Admin;

use App\Model\Organizations;
use App\Services\AuthenticationHelper;
use App\Services\OrganizationsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\AADGraphClient;
use Illuminate\Support\Facades\Input;
use Lcobucci\JWT\Parser;
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
          }else{
              $_SESSION[SiteConstants::Session_RedirectURL] = '/admin';
            }
        }
        $arrData = array(
            'IsAdminConsented'=>$IsAdminConsented
        );

        return view('admin.index',$arrData);
    }

    public function consent()
    {
        $_SESSION[SiteConstants::Session_RedirectURL] = '/admin/consent';
        $consented = false;
        if(Input::get('consented')){
            $consented=true;
        }
        $arrData = array(
            'consented'=>$consented
        );
        return view('admin.consent',$arrData);

     }

    public function AdminConsent()
    {
        $redirectUrl = $_SERVER['APP_URL'] . '/admin/processcode';
        $state = uniqid();
        $_SESSION[SiteConstants::Session_State] =$state;

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => Constants::CLIENT_ID,
            'clientSecret' => Constants::CLIENT_SECRET,
            'redirectUri' => $redirectUrl,
            'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => ''
        ]);
        $url = $provider->getAuthorizationUrl([
            'response_type'=>'code',
            'resource'=>Constants::AADGraph,
            'state'=>$state,
            'prompt'=>SiteConstants::AdminConsent
        ]);

        header('Location: ' . $url);
        exit();
    }

    public function  ProcessCode()
    {
          $code=  Input::get('code');
          $state=  Input::get('state');
         if(!isset($_SESSION[SiteConstants::Session_State]) || $_SESSION[SiteConstants::Session_State] !=$state || !$code)
         {
             return back()->with('msg','Invalid operation. Please try again.');
         }
        if($code){
            $redirectUrl = $_SERVER['APP_URL'] .'/admin/processcode';
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => Constants::CLIENT_ID,
                'clientSecret' => Constants::CLIENT_SECRET,
                'redirectUri' => $redirectUrl,
                'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
                'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
                'urlResourceOwnerDetails' => ''
            ]);
            $microsoftToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
                'resource' => Constants::RESOURCE_ID
            ]);
            $idToken  = $microsoftToken->getValues()['id_token'];
            $parsedToken = (new Parser())->parse((string)$idToken);
            $tenantId =  $parsedToken->getClaim('tid');
             (new OrganizationsServices)->SetTenantConsented( $tenantId);
        }

        $redirectURL='/';
        if(isset($_SESSION[SiteConstants::Session_RedirectURL])) {
            $redirectURL = $_SESSION[SiteConstants::Session_RedirectURL];
            unset($_SESSION[SiteConstants::Session_RedirectURL]);
        }
        header('Location: ' . $redirectURL .'?consented=true');
        exit();
    }


}
