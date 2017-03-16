<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Token;

class TokenCache extends Model
{
    public $timestamps=false;
    protected $table='TokenCache';

    public  function UpdateOrInsertCache($userId, $refreshToken, $accessToken)
    {
        $tokenCache = $this->where('UserId',$userId)->first();
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
            $this->save();
        }

    }

    public function GetToken($UserId)
    {

    }


}
