<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Author extends Model
{
    use SoftDeletes;
    const CacheItemPeriod = 50;

    protected $fillable = [
        'name'
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function ($author) {

        });

        self::updated(function ($author) {
            foreach ($author->events as $event) {
                Event::forgetCache($event->id);
            }

            foreach ($author->places as $place) {

            }
        });

        self::deleting(function ($author) {

            foreach ($author->events as $event) {
                Event::forgetCache($event->id);
            }

            foreach ($author->places as $place) {

            }

            $author->authorables()->delete();
        });
    }

    public static function getById($id)
    {
        $author = Author::findOrFail($id, [
            'id', 'name'
        ]);

        return $author;
    }

    public function events()
    {
        return $this->morphedByMany('App\Models\Event', 'authorable');
    }

    public function places()
    {
        return $this->morphedByMany('App\Models\Place', 'authorable');
    }

    public function authorables()
    {
        return $this->hasMany('App\Models\Authorable', 'author_id');
    }
}