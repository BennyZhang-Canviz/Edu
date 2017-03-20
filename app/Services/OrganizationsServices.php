<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2017/3/20
 * Time: 14:50
 */

namespace App\Services;
use App\Model\Organization;

class OrganizationsServices
{


    public function CreateBy($tenant,$tenantId)
    {
//        $graph = new AADGraphClient;
//        $tenant =  $graph->GetTenantByUserId($o365UserId);
//        $tenantId = $graph->GetTenantId($tenant);
//        (new OrganizationsServices)->Create($tenant,$tenantId);
        $org = new Organization();
        $org->name = $tenant->getDisplayName();
        $org->tenantId = $tenantId;
        $org->isAdminConsented =false;
        $org->created = date("Y-m-d h:i:s");
        $org->save();
    }
}