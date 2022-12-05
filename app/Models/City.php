<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class City extends Model
{
    use Searchable;
    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo('App\Models\Province');
    }

    public function citiables()
    {
        return $this->hasMany('App\Models\Citiable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

}
