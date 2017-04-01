<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Services;

use Microsoft\Graph\Connect\Constants;

class AuthenticationHelper
{
    public  function GetProvider($redirectUri=Constants::REDIRECT_URI)
    {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => Constants::CLIENT_ID,
            'clientSecret' => Constants::CLIENT_SECRET,
            'redirectUri' => $redirectUri,
            'urlAuthorize' => Constants::AUTHORITY_URL . Constants::AUTHORIZE_ENDPOINT,
            'urlAccessToken' => Constants::AUTHORITY_URL . Constants::TOKEN_ENDPOINT,
            'urlResourceOwnerDetails' => ''
        ]);
        return $provider;
    }
}

