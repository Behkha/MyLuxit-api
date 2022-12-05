<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Rating extends Model
{
    use SoftDeletes;
    protected $table = 'ratings';
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        self::created(function ($rating) {
            $type = ucfirst($rating->ratable_type);
            $id = $rating->ratable_id;
        });

        self::updated(function ($rating) {
            $type = ucfirst($rating->ratable_type);
            $id = $rating->ratable_id;
        });

    }
}
