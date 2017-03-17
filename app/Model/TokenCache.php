<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Token;

class TokenCache extends Model
{
    public $timestamps=false;
    protected $table='TokenCache';

}
