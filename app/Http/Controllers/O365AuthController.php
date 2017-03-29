<?php

namespace App\Http\Controllers;


use App\Services\AuthenticationHelper;
use App\Services\CookieService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Connect\Constants;
use Lcobucci\JWT\Parser;
use App\Config\SiteConstants;
use App\User;
use Illuminate\Support\Facades\Crypt;
use App\Model\TokenCache;
use App\Services\TokenCacheServices;
use App\Services\AADGraphClient;
use App\Services\OrganizationsServices;
use Socialize;


class O365AuthController extends Controller
{

    public function oauth()
    {
        $user = Socialite::driver('O365')->user();

        $refreshToken = $user->refreshToken;
        $_SESSION[SiteConstants::Session_Refresh_Token] = $refreshToken;
        $o365UserId  = $user->id;
        $o365Email = $user->email;

        $microsoftTokenArray = (new TokenCacheServices())->refreshToken($user->id,$refreshToken,Constants::RESOURCE_ID,true);
        $tokensArray = $this->getTokenArray($user,$microsoftTokenArray);
        $_SESSION[SiteConstants::Session_Tokens_Array] = $tokensArray;

        $graph = new AADGraphClient;
        $tenant = $graph->GetTenantByToken($microsoftTokenArray['token']);
        $tenantId = $graph->GetTenantId($tenant);
        $orgId = (new OrganizationsServices)->CreateByTenant($tenant, $tenantId);
        $_SESSION[SiteConstants::Session_OrganizationId] = $orgId;
        $_SESSION[SiteConstants::Session_TenantId] = $tenantId;

        $this->linkLocalUserToO365($user,$o365Email,$o365UserId,$orgId,$refreshToken,$tokensArray);

        $userInDB = User::where('o365UserId', $o365UserId)->first();
        //If user exists on db, check if this user is linked. If linked, go to schools/index page, otherwise go to link page.
        //If user doesn't exists on db, add user information like o365 user id, first name, last name to session and then go to link page.
        (new TokenCacheServices)->UpdateOrInsertCache($o365UserId, $refreshToken, $tokensArray);
        if ($userInDB) {
            $o365UserIdInDB = $userInDB->o365UserId;
            $o365UserEmailInDB = $userInDB->o365Email;
            if ($o365UserEmailInDB === '' || $o365UserIdInDB === '') {
                return redirect('/link');
            } else {
                Auth::loginUsingId($userInDB->id);
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }
        } else {
            $_SESSION[SiteConstants::Session_O365_User_ID] = $o365UserId;
            $_SESSION[SiteConstants::Session_O365_User_Email] = $o365Email;
            $_SESSION[SiteConstants::Session_O365_User_First_name] = $user->user['givenName'];
            $_SESSION[SiteConstants::Session_O365_User_Last_name] = $user->user['surname'];
            return redirect('/link');
        }
    }

    private function getTokenArray($user,$microsoftTokenArray)
    {
        $ts = $user->accessTokenResponseBody['expires_on'];
        $date = new \DateTime("@$ts");
        $aadTokenExpires = $date->format('Y-m-d H:i:s');
        $format = '{"https://graph.windows.net":{"expiresOn":"%s","value":"%s"},"https://graph.microsoft.com":{"expiresOn":"%s","value":"%s"}}';
        return sprintf($format, $aadTokenExpires,  $user->token,$microsoftTokenArray['expires'], $microsoftTokenArray['token'] );

    }

    private function linkLocalUserToO365($user,$o365Email,$o365UserId,$orgId,$refreshToken,$tokensArray)
    {
        if (Auth::check()) {

            //A local user must link to and o365 account that is not linked.
            if (User::where('o365Email', $o365Email)->first())
                return back()->with('msg', 'Failed to link accounts. The Office 365 account ' . $o365Email . ' is already linked to another local account.');

            $localUser = Auth::user();
            $localUser->o365UserId = $o365UserId;
            $localUser->o365Email = $o365Email;
            $localUser->firstName = $user->user['givenName'];
            $localUser->lastName =  $user->user['surname'];
            $localUser->password = '';
            $localUser->OrganizationId = $orgId;
            $localUser->save();
            (new TokenCacheServices)->UpdateOrInsertCache($o365UserId, $refreshToken, $tokensArray);

            return redirect("/schools");
        }
    }

    public function o365LoginHint()
    {
        $cookieServices = new CookieService();
        $email = $cookieServices->GetCookiesOfEmail();
        $userName = $cookieServices->GetCookiesOfUsername();
        $data = ["email" => $email, "userName" => $userName];
        return view('auth.o365loginhint', $data);

    }

    public function o365Login()
    {
        return  Socialize::with('O365')->redirect();
    }
    public function differentAccountLogin()
    {
        $cookieServices = new CookieService();
        $cookieServices->ClearCookies();
        return redirect('/login');
    }

}
