<?php

namespace App\ViewModel;


class EducationUser extends ParsableObject
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mappingTable =
            [
                "mail" => "mail",
                "educationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType",
                "displayName" => "displayName",
                "educationGrade" => "extension_fe2174665583431c953114ff7268b7b3_Education_Grade",
                "schoolId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
                "o365UserId" => "objectId"
            ];
    }

    public $mail;

    public $educationObjectType;

    public $displayName;

    public $educationGrade;

    public $schoolId;

    public $o365UserId;

    public $position;

    public $favoriteColor;
}