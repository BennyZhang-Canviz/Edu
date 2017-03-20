<?php
namespace App\Services;
use App\Model\TokenCache;
use Microsoft\Graph\Connect\Constants;

class TokenCacheServices
{
    public  function UpdateOrInsertCache($userId, $refreshToken, $accessToken)
    {
        $tokenCache = TokenCache::where('UserId',$userId)->first();
        if($tokenCache)
        {
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessToken;
            $tokenCache->save();
        }
        else{
            $tokenCache = new TokenCache();
            $tokenCache->refreshToken = $refreshToken;
            $tokenCache->accessTokens = $accessToken;
            $tokenCache->UserId = $userId;
            $tokenCache->save();
        }

    }

    public function GetMicrosoftToken($userId)
    {
        return $this-> getToken($userId,Constants::RESOURCE_ID);
    }
    public function GetAADToken($userId)
    {
        return  $this->getToken($userId,Constants::AADGraph);
    }

    private function getToken($userId,$resource)
    {
        $tokenCache =TokenCache::where('UserId',$userId)->first();
        if($tokenCache){
            //1. Check if token is expired. If expired, get a new token with refresh token.
            $token = $tokenCache->accessTokens;
            $array=array();
            $array=json_decode($token,true);
            $expired =  $array[$resource]['expiresOn'];

            $date1 =  gmdate($expired);
            $date2 =  gmdate(date("Y-m-d h:i:s"));
            if(strtotime($date1) < strtotime($date2)){
                return $this->RefreshToken($userId,$tokenCache->refreshToken,$resource);
            }
            else
                return $array[$resource]['value'];
        }
        else
            return null;
    }


    private  function  RefreshToken($userId, $refreshToken, $resource)
    {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => Constants::CLIENT_ID,
            'clientSecret'            => Constants::CLIENT_SECRET,
            'redirectUri'             => Constants::REDIRECT_URI,
            'urlAuthorize'            => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken'          => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => ''
        ]);
        $aadGraphTokenResult='';
        $aadTokenExpires ='';

        $microsoftTokenResult='';
        $microsoftTokenExpires='';

        $newRefreshToken = $refreshToken;
        if($resource ===Constants::RESOURCE_ID){
            $microsoftToken = $provider->getAccessToken('refresh_token', [
                'refresh_token'     =>$refreshToken,
                'resource' =>Constants::RESOURCE_ID
            ]);
            $ts = $microsoftToken->getExpires();
            $date = new \DateTime("@$ts");
            $microsoftTokenExpires =  $date->format('Y-m-d H:i:s');
            $microsoftTokenResult =$microsoftToken->getToken();
            $newRefreshToken = $microsoftToken->getRefreshToken();
        }
        else{
            $aadGraphToken = $provider->getAccessToken('refresh_token', [
                'refresh_token'     => $refreshToken,
                'resource' =>Constants::AADGraph
            ]);
            $ts = $aadGraphToken->getExpires();
            $date = new \DateTime("@$ts");
            $aadTokenExpires = $date->format('Y-m-d H:i:s');
            $aadGraphTokenResult =$aadGraphToken->getToken();
            $newRefreshToken = $aadGraphToken->getRefreshToken();
        }
        $tokenCache = TokenCache::where('UserId',$userId)->first();
        if($tokenCache){
            $token = $tokenCache->accessTokens;
            $array=array();
            $array=json_decode($token,true);
            if($resource ===Constants::RESOURCE_ID){
                //aad oper
                $aadTokenExpires = $array[Constants::AADGraph]['expiresOn'];
                $aadGraphTokenResult = $array[Constants::AADGraph]['value'];
            }else{
                $microsoftTokenExpires = $array[Constants::RESOURCE_ID]['expiresOn'];
                $microsoftTokenResult = $array[Constants::RESOURCE_ID]['value'];
            }
        }
        $format = '{"https://graph.windows.net":{"expiresOn":"%s","value":"%s"},"https://graph.microsoft.com":{"expiresOn":"%s","value":"%s"}}';
        $tokensArray = sprintf($format, $aadTokenExpires,$aadGraphTokenResult, $microsoftTokenExpires,$microsoftTokenResult);
        $this->UpdateOrInsertCache($userId,$newRefreshToken,$tokensArray);
        if($resource ===Constants::RESOURCE_ID){
            return $microsoftTokenResult;
        }
        else{
            return $aadGraphTokenResult;
        }
    }
}