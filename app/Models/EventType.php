<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class EventType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public static function boot()
    {
        parent::boot();

        self::updated(function ($type) {
            foreach ($type->events as $event) {
                Event::forgetCache($event->id);
            }
        });

        self::deleted(function ($type) {

        });
    }

    public function events()
    {
        return $this->hasMany('App\Models\Event', 'type_id');
    }
}
