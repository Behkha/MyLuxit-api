<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Photographer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public static function boot()
    {
        parent::boot();

        self::updated(function ($photographer) {
            foreach ($photographer->events as $event) {
                Event::forgetCache($event->id);
            }

            foreach ($photographer->places as $place) {
            }
        });

        self::deleting(function ($photographer) {
            foreach ($photographer->events as $event) {
                Event::forgetCache($event->id);
            }

            foreach ($photographer->places as $place) {
            }

            $photographer->photographerables()->delete();
        });
    }

    public function events()
    {
        return $this->morphedByMany('App\Models\Event', 'photographerable');
    }

    public function places()
    {
        return $this->morphedByMany('App\Models\Place', 'photographerable');
    }

    public function photographerables()
    {
        return $this->hasMany('App\Models\Photographerable', 'photographer_id');
    }
}
