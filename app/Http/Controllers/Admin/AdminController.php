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

    public function EnableUserAccess()
    {
        $user = Auth::user();
        $o365UserId = $user->o365UserId;
        $token = (new TokenCacheServices)->GetAADToken($o365UserId);
        $tenantId = (new AADGraphClient)->GetTenantIdByUserId($o365UserId);
        $url=Constants::AADGraph .'/'.$tenantId.'/servicePrincipals/?api-version=1.6&$filter=appId%20eq%20\''.Constants::CLIENT_ID . '\'';
        $client = new \GuzzleHttp\Client();
        $app=null;
        $appId='';
        $appName='';
        $authHeader= [
            'headers' => [
                'Content-Type' => 'application/json;odata.metadata=minimal;odata.streaming=true',
                'Authorization' => 'Bearer ' . $token
            ]
        ];
        try{
            $result = $client->request('GET', $url, $authHeader);
            $app= json_decode($result->getBody())->value;
            $servicePrincipalId = $app[0]->objectId;
            $servicePrincipalName = $app[0]->appDisplayName;
        }
        catch(\Exception $e){
            return back()->with('msg',SiteConstants::NoPrincipalError);
        }
        $this->AddAppRoleAssignmentForUsers($authHeader,null,$tenantId,$servicePrincipalId,$servicePrincipalName);
        try{
            // $this->AddAppRoleAssignmentForUsers($authHeader,null,$tenantId,$servicePrincipalId,$servicePrincipalName);
        }
        catch(\Exception $e){
             return back()->with('msg',SiteConstants::EnableUserAccessFailed);
          }
    }

    private function AddAppRoleAssignmentForUsers($authHeader, $nextLink,$tenantId,$servicePrincipalId,$servicePrincipalName)
    {

        $url = Constants::AADGraph .'/'.$tenantId.'/users?api-version=1.6&$expand=appRoleAssignments';
        if ($nextLink) {
            $url = $url . "&" . $this->GetSkipToken($nextLink);
        }
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $url, $authHeader);
        $response = json_decode($result->getBody());
        $users = $response->value;
        //todo: not completed.
        //$this->AddAppRoleAssignment($authHeader,$users,$servicePrincipalId,$servicePrincipalName,$tenantId);
        if(isset(get_object_vars($response)['odata.nextLink']))
            $nextLink =get_object_vars($response)['odata.nextLink'];
        else{
            $nextLink=null;
        }
        if($nextLink){
          $this->  AddAppRoleAssignmentForUsers($authHeader,$nextLink,$tenantId,$servicePrincipalId,$servicePrincipalName);
        }

    }

    private function AddAppRoleAssignment($authHeader,$users,$servicePrincipalId,$servicePrincipalName,$tenantId)
    {
        $count = count($users);
        $client = new \GuzzleHttp\Client();

        for($i=0;$i<$count;$i++){
            $user = $users[$i];
            if($user->objectId !='adbd9250-c0a9-46a9-addf-743fc6b31ed6')
                continue;
            $roleAssignment = $user->appRoleAssignments;
            $roles = count($roleAssignment);
            $servicePrincipalExists = false;
            for($j=0;$j<$roles;$j++)
            {
                if($roleAssignment[$j]->resourceId == $servicePrincipalId){
                   return;
                }
            }
           if(!$servicePrincipalExists){

             if(!isset($roleAssignment['odata.nextLink'])){
                $this->DoAddRole($authHeader,$user,$servicePrincipalId,$servicePrincipalName,$tenantId);
             }else{
                $url = Constants::AADGraph .'/'.$tenantId.'/users/'.$user->objectId.'/appRoleAssignments?api-version=1.6&$filter=resourceId%20eq%20guid\''.$servicePrincipalId.'\'';
                $result = $client->request('GET', $url, $authHeader);
                 $response = json_decode($result->getBody());
                 if(! $response->value){
                     $this->DoAddRole($authHeader,$user,$servicePrincipalId,$servicePrincipalName,$tenantId);
                 }
             }
           }
        }
    }

    private function DoAddRole($authHeader,$user,$servicePrincipalId,$servicePrincipalName,$tenantId)
    {

        $client = new \GuzzleHttp\Client();
        $body = [
            'odata.type'=> 'Microsoft.DirectoryServices.AppRoleAssignment',
            'creationTimestamp'=> gmdate(date("Y-m-d h:i:s")),
            'principalDisplayName'=> $user->displayName,
            'principalId'=> $user->objectId,
            'principalType'=> 'User',
            'resourceId'=> $servicePrincipalId,
            'resourceDisplayName'=> $servicePrincipalName
        ];
        $url =  Constants::AADGraph .'/'.$tenantId .'/users/'.$user->objectId.'/appRoleAssignments?api-version=1.6';
        $res =  $client->request('POST', $url, $authHeader,$body);
        $a=1;
    }


    private function GetSkipToken($nextLink)
    {
        $pattern = '/\$skiptoken=[^&]+/';
        preg_match($pattern, $nextLink, $match);
        if(count($match)==0)
            return '';
        return $match[0];
    }


}
