<?php

namespace App\Http\Controllers;

use App\Config\SiteConstants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\User;


class LinkController extends Controller
{
    public  function index()
    {

        $isLocalUserExists = false;
        $areAccountsLinked = false;
        //If session exists for O365 user, it's the first time that an O365 user login.
        $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
        if($o365userId){
            $user  = User::where('email', $_SESSION[SiteConstants::Session_O365_User_Email])->first();
            if($user){
                $isLocalUserExists = true;
            }
        }

        //check if a user is login.
        if (Auth::check()) {

        }
        $arrData = array(
            'isLocalUserExists'=>$isLocalUserExists,
            'areAccountsLinked'=>$areAccountsLinked
        );
        return view("link.index",$arrData);
    }

    public function createLocalAccount()
    {
        if($input =Input::all()){
            $favoriteColor = $input['FavoriteColor'];
            $o365UserId = $_SESSION[SiteConstants::Session_O365_User_ID];
            $o365Email = $_SESSION[SiteConstants::Session_O365_User_Email];
            $firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
            $lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
            User::create([
                'firstName' => $firstName,
                'lastName' => $lastName,
                //'password' => bcrypt('secret'),
                'o365UserId' =>$o365UserId,
                'o365Email'=>$o365Email,
                'email' =>$o365Email,
                'favorite_color'=>$favoriteColor
            ]);
            //TODO: Need to handle cache here
            return redirect('/schools');
        }else{
            return view("link.createlocalaccount");
        }
        }


    public function loginLocal()
    {

        $o365email = $_SESSION[SiteConstants::Session_O365_User_Email];
        $user  = User::where('email', $o365email)->first();

        //If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
        if($user){
            $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
            $user->o365Email=$o365email;
            $user->save();

            //Todo:login and then go to schools page.
        }
        //If there's no local account with same email as o365 email, go to login local page.
        else{
            echo 'go to login page';
        }

    }
}
