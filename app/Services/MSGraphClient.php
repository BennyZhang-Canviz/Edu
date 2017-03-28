<?php
namespace App\Services;

use App\Services\TokenCacheServices;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class MSGraphClient
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->tokenCacheService = new TokenCacheServices();
        $this->graph = new Graph();
    }

    /**
     *
     * Get the photo of a specified user.
     *
     * @param string $o365UserId The Office 365 user id of the user
     *
     * @return The photo of the user
     */
    public function getUserPhoto($o365UserId)
    {
        $token = $this->getToken();
        if ($token)
        {
            try
            {
                return $this->graph->setAccessToken($token)
                    ->createRequest("get", "/users/$o365UserId /photo/\$value")
                    ->setReturnType(Stream::class)
                    ->execute();
            }
            catch (Exception $e)
            {
                return null;
            }
        }
    }
    /**
     * Get access token
     *
     * @return string The access token
     */
    private function getToken()
    {
        $user = Auth::user();
        if (strlen($user->o365UserId) == 0)
        {
            return null;
        }
        return $this->tokenCacheService->GetMicrosoftToken($this->o365UserId);
    }

    private $tokenCacheService;
    private $graph;
}