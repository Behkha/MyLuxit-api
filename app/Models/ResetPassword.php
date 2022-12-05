<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResetPassword extends Model
{
    use SoftDeletes;
    protected $guarded = [];
}
