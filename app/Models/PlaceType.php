<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class PlaceType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public static function boot()
    {
        parent::boot();

        self::updated(function ($type) {
            foreach ($type->places as $place) {
                foreach ($place->posts as $post) {

                }
                foreach ($place->events as $event) {
                    Event::forgetCache($event->id);
                }
            }
        });

        self::deleting(function ($type) {
            foreach ($type->places as $place) {
                foreach ($place->posts as $post) {

                }
            }
        });
    }

    public function places()
    {
        return $this->hasMany('App\Models\Place', 'type_id');
    }
}
