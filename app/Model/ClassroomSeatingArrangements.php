<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ClassroomSeatingArrangements extends Model
{
    public $timestamps=false;
    protected $table='ClassroomSeatingArrangements';
    protected $fillable = ['o365UserId', 'classId', 'position'];
}
