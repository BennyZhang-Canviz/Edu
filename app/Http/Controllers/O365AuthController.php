<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Connect\Constants;
use Lcobucci\JWT\Parser;

class O365AuthController extends Controller
{
    public function oauth()
    {
        //We store user name, id, and tokens in session variables
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => Constants::CLIENT_ID,
            'clientSecret'            => Constants::CLIENT_SECRET,
            'redirectUri'             => Constants::REDIRECT_URI,
            'urlAuthorize'            => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken'          => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => '',
            'scopes'                  => Constants::SCOPES
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['code'])) {
            $authorizationUrl = $provider->getAuthorizationUrl();

            // The OAuth library automaticaly generates a state value that we can
            // validate later. We just save it for now.
            $_SESSION['state'] = $provider->getState();

            header('Location: ' . $authorizationUrl);
            exit();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['code'])) {
            // Validate the OAuth state parameter
            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['state'])) {
                unset($_SESSION['state']);
                exit('State value does not match the one initially sent');

            }


            // With the authorization code, we can retrieve access tokens and other data.
            try {
                // Get an access token using the authorization code grant
                $microsoftToken = $provider->getAccessToken('authorization_code', [
                    'code'     => $_GET['code']
                ]);


                $graphToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token'     => $microsoftToken->getRefreshToken(),
                    'resource' =>Constants::AADGraph
                ]);


                $refreshToken = $graphToken->getRefreshToken();

                $idToken = $microsoftToken->getValues()['id_token'];
                $token = (new Parser())->parse((string) $idToken); // Parses from a string


                //$_SESSION['access_token'] = $microsoftToken->getToken();
//
//                header('Location: http://localhost:8000/email');
//                exit();
            } catch (Exception $e) {
                echo 'Something went wrong, couldn\'t get tokens: ' . $e->getMessage();
            }
        }
    }
}
