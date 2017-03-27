<?php
namespace App\ViewModel;


class Section extends ParsableObject
{
    public function __construct()
    {
        $this->mappingTable =
            [
                "objectId" => "objectId",
                "objectType" => "objectType",
                "EducationObjectType" => "extension_fe2174665583431c953114ff7268b7b3_Education_ObjectType",
                "DisplayName" => "displayName",
                "Email" => "mail",
                "SecurityEnabled" => "securityEnabled",
                "MailNickname" => "mailNickname",
                "Period" => "extension_fe2174665583431c953114ff7268b7b3_Education_Period",
                "CourseNumber" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseNumber",
                "CourseDescription" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseDescription",
                "CourseName" => "extension_fe2174665583431c953114ff7268b7b3_Education_CourseName",
                "CourseId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_CourseId",
                "TermEndDate" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermEndDate",
                "TermStartDate" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermStartDate",
                "TermName" => "extension_fe2174665583431c953114ff7268b7b3_Education_TermName",
                "TermId" => "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_TermId",
                "SectionNumber" => "extension_fe2174665583431c953114ff7268b7b3_Education_SectionNumber",
                "SectionName" =>"extension_fe2174665583431c953114ff7268b7b3_Education_SectionName",
                "SectionId" =>"extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SectionId",
                "SchoolId" =>"extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_SchoolId",
                "SyncSource" =>"extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource",
                "AnchorId" =>"extension_fe2174665583431c953114ff7268b7b3_Education_AnchorId",
                "EducationStatus" =>"extension_fe2174665583431c953114ff7268b7b3_Education_Status",
                "Users" => "members"
            ];
    }

    public $objectId;
    public $objectType ;
    public $EducationObjectType ;
    public $DisplayName;
    public $Email ;
    public $SecurityEnabled ;
    public $MailNickname ;
    public $Period;
    public $CourseNumber ;
    public $CourseDescription;
    public $CourseName ;
    public $CourseId ;
    public $TermEndDate;
    public $TermStartDate ;
    public $TermName;
    public $TermId;
    public $SectionNumber ;
    public $SectionName ;
    public $SectionId;
    public $SchoolId;
    public $SyncSource;
    public $AnchorId;
    public $EducationStatus;
    public $Users;

    public function CombinedCourseNumber()
    {
        return strtoupper(substr($this->CourseName,3)) + $this->GetCourseNumber($this->CourseNumber);
    }

    private function GetCourseNumber($courseNumber)
    {
        $pattern = '/\d+/';
        preg_match($pattern, $courseNumber, $match);
        if (count($match) == 0)
            return '';
        return $match[0];
    }
}