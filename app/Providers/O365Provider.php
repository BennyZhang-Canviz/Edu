<?php

namespace App\Providers;


use App\Services\CookieService;

class O365Provider extends \SocialiteProviders\Azure\Provider
{

    protected $version = '1.6';
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        $url =  parent::getAuthUrl($state);
        //login_hint
        $mail = (new CookieService)->GetCookiesOfEmail();
        if($mail){
            if(strpos($url,'?')>0){
                $url = $url . '&' . 'login_hint=' . $mail;
            }else{
                $url = $url . '?' . 'login_hint=' . $mail;
            }
        }
        return $url;
    }

}
