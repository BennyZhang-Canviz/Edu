<?php

namespace App\Http\Controllers\Admin;

use App\Model\Organizations;
use App\Services\AuthenticationHelper;
use App\Services\OrganizationsServices;
use App\Services\TokenCacheServices;
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
        $msg='';
        $consented = Input::get('consented');
        if($consented !=null ){
            if( $consented==='true')
                $msg = SiteConstants::AdminConsentSucceedMessage;
            else
                $msg = SiteConstants::AdminUnconsentMessage;
        }
        $arrData = array(
            'IsAdminConsented'=>$IsAdminConsented,
            'msg'=>$msg
        );

        return view('admin.index',$arrData);
    }

    public function consent()
    {
        $_SESSION[SiteConstants::Session_RedirectURL] = '/admin/consent';
        $consented = false;
        $msg = '';
        if(Input::get('consented')){
            $consented=true;
            $msg = SiteConstants::AdminConsentSucceedMessage;
        }
        $arrData = array(
            'consented'=>$consented,
            'msg'=>$msg
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
             (new OrganizationsServices)->SetTenantConsentResult( $tenantId,true);
        }

        $redirectUrl='/';
        if(isset($_SESSION[SiteConstants::Session_RedirectURL])) {
            $redirectUrl = $_SESSION[SiteConstants::Session_RedirectURL];
            unset($_SESSION[SiteConstants::Session_RedirectURL]);
        }
        header('Location: ' . $redirectUrl .'?consented=true');
        exit();
    }

    public function  AdminUnconsent()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = (new TokenCacheServices)->GetAADToken($o365UserId);
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        $url='https://graph.windows.net/'.$tenantId.'/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\''.Constants::CLIENT_ID . '\'';
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
       $app= json_decode($result->getBody())->value;
       $appId = $app[0]->objectId;
       $url ='https://graph.windows.net/'.$tenantId.'/servicePrincipals/'.$appId.'?api-version=1.6';
        $result = $client->request('DELETE', $url, [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        (new OrganizationsServices)->SetTenantConsentResult( $tenantId,false);

        header('Location: '  . '/admin?consented=false');
        exit();

    }

}
