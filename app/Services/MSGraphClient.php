<?php
namespace App\Services;

use App\Services\TokenCacheServices;
use Illuminate\Support\Facades\Auth;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Config\SiteConstants;
use App\Config\Roles;
use App\Config\O365ProductLicenses;
use App\Services\UserRolesServices;
use Microsoft\Graph\Connect\Constants;

class MSGraphClient
{
}