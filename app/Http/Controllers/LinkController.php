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
        $localUserEmail = '';
        //If session exists for O365 user, it's the first time that an O365 user login.
        $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
        if($o365userId){
            $user  = User::where('email', $_SESSION[SiteConstants::Session_O365_User_Email])->first();
            if($user){
                $isLocalUserExists = true;
                $localUserEmail = $_SESSION[SiteConstants::Session_O365_User_Email];
            }
        }

        //check if a user is login.
        if (Auth::check()) {

        }
        $arrData = array(
            'isLocalUserExists'=>$isLocalUserExists,
            'areAccountsLinked'=>$areAccountsLinked,
            'localUserEmail' =>$localUserEmail
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
                'password' => bcrypt('secret'),
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
        if($input =Input::all()){ //post from page.
           $email = $input['email'];
           $password = $input['password'];
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];
            if (Auth::attempt($credentials)) {
               $user = Auth::user();
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->save();
                Auth::loginUsingId($user->id);
                //todo: need to handel cache here.
                if (Auth::check()) {
                    return redirect("/schools");
                }
            }else{
                return back()->with('msg','Invalid login attempt.');
            }

        }
        else{

            $user  = User::where('email', $o365email)->first();

            //If there's a local user with same email as o365 email on db, link this account to o365 account directly and then go to schools page.
            if($user){
                $user->o365UserId=$_SESSION[SiteConstants::Session_O365_User_ID];
                $user->o365Email=$o365email;
                $user->firstName = $_SESSION[SiteConstants::Session_O365_User_First_name];
                $user->lastName = $_SESSION[SiteConstants::Session_O365_User_Last_name];
                $user->save();
                Auth::loginUsingId($user->id);
                //todo need to handle cache here.
                if (Auth::check()) {
                    return redirect("/schools");
                }

            }
            return view('link.loginlocal');
        }


    }
}
