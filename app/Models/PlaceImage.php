<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlaceImage
 * @package App\Models
 * --------------- Deprecated, DO NOT USE !  ---------------
 */
class PlaceImage extends Model
{

    const Statuses = [
        'pending' => 1,
        'confirmed' => 2,
        'rejected' => 3,
    ];
    protected $guarded = [];
    protected $casts = [
        'media' => 'array'
    ];

    public function imagable()
    {
        return $this->morphTo();
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status_id', self::Statuses['confirmed']);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
