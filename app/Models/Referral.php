<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $guarded = [];

    const STATUSES = [
        'active' => 1,
        'deactive' => 2,
    ];
}
