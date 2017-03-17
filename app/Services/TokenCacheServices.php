<?php
namespace App\Http\Services;
use App\Model\TokenCache;

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

    public function GetToken($userId)
    {
        $tokenCache =TokenCache::where('UserId',$userId)->first();
        if($tokenCache)
            return $tokenCache->refreshToken;
        else
            return null;
    }
}