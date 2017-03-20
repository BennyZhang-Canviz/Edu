<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/3/20
 * Time: 14:50
 */

namespace App\Services;
use App\Model\Organizations;

class OrganizationsServices
{

    public function CreateByTenant($tenant,$tenantId)
    {
        $org = Organizations::where('tenantId',$tenantId)->first();
        if(!$org){
            $org = new Organizations();
            $org->name = $tenant[0]->getDisplayName();
            $org->tenantId = $tenantId;
            $org->isAdminConsented =false;
            $org->created = date("Y-m-d h:i:s");
            $org->save();
        }
    }

    public function GetOrganization()
    {

    }
}