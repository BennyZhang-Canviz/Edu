<?php
/**
 * Created by PhpStorm.
 * User: stlui
 * Date: 3/24/2017
 * Time: 11:32 AM
 */

namespace App\ViewModel;


class Teacher extends EducationUser
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->mappingTable["teacherId"] = "extension_fe2174665583431c953114ff7268b7b3_Education_SyncSource_TeacherId";
    }

    /**
     * Get the user id
     *
     * @return string The user id
     */
    public function getUserId()
    {
        return $this->teacherId;
    }

    public $teacherId;
}