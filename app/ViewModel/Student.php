<?php
/**
 * Created by PhpStorm.
 * User: stlui
 * Date: 3/24/2017
 * Time: 11:31 AM
 */

namespace App\ViewModel;


class Student extends EducationUser
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->mappingTable["studentId"] = "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_StudentId";
    }

    /**
     * Get the user id
     *
     * @return string The user id
     */
    public function getUserId()
    {
        return $this->studentId;
    }

    public $studentId;
}