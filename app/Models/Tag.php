<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class Tag extends Model
{
    use Searchable;
    protected $fillable = [
        'name'
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function ($tag) {

        });

        self::updated(function ($tag) {


            if (!app()->runningInConsole()) {
                foreach ($tag->places as $place) {

                }

                foreach ($tag->events as $event) {
                    Event::forgetCache($event->id);
                }

                foreach ($tag->posts as $post) {

                }
            }
        });

        self::deleted(function ($tag) {


            foreach ($tag->places as $place) {
            }

            foreach ($tag->events as $event) {
                Event::forgetCache($event->id);
            }

            foreach ($tag->posts as $post) {
            }

        });
    }

    public function posts()
    {
        return $this->morphedByMany('App\Models\Post', 'tagable');
    }

    public function places()
    {
        return $this->morphedByMany('App\Models\Place', 'tagable');
    }

    public function events()
    {
        return $this->morphedByMany('App\Models\Event', 'tagable');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}