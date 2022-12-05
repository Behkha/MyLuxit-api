<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citiable extends Model
{
    protected $guarded = [];

    public function citiable()
    {
        return $this->morphTo();
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }
}
