<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Laravel\Scout\Searchable;
use Morilog\Jalali\Jalalian;


class Event extends Model
{
    use SoftDeletes, Searchable;


    const CacheItemPeriod = 10;
    const CachePagePeriod = 5;
    const CacheVisitsPeriod = 60;
    const CacheRatingPeriod = 20;
    const DetailTypes = [
        'address' => 'آدرس',
        'tell' => 'شماره تلفن',
        'price' => 'قیمت بلیط',
        'duration' => 'تاریخ شروع و پایان',
        'custom_link' => '',
        'service_provider' => 'ارائه دهنده خدمت',
    ];
    protected $guarded = [];
    protected $casts = [
        'media' => 'array',
        'links' => 'array',
        'meta' => 'array',
        'details' => 'array'
    ];

    public static function getById($id)
    {

        $event = Cache::tags(['Event', "Event:$id"])->remember("Event:$id", self::CacheItemPeriod, function () use ($id) {
            return self::with([
                'place', 'type', 'authors', 'photographers', 'tags', 'place.type', 'characters', 'characters.character',
                'cities', 'language'
            ])->findOrFail($id);
        });


        return $event;
    }

    public static function boot()
    {
        parent::boot();

        self::created(function ($event) {
            \App\Models\Searchable::create([
                'searchable_type' => 'event',
                'searchable_id' => $event->id
            ]);
            Artisan::call('images:removehttp');
            Category::indexRelatedCategoriesTo($event);
        });

        self::deleted(function ($event) {
            $event->searchables()->delete();
            $event->comments()->delete();
            $event->bookmarks()->delete();
            $event->ratings()->delete();
            $event->tags()->delete();
            foreach ($event->posts()->get() as $post) {
                $post->delete();
            };
            Post::flushPostsIndex();
            CharacterProperty::where('property_type', 'event')->where('property_id', $event->id)->delete();

            Category::indexRelatedCategoriesTo($event);
        });

        self::updated(function ($event) {
            Post::flushPostsIndex();
            self::forgetCache($event->id);
            if (!app()->runningInConsole()) {
                foreach ($event->posts()->get() as $post) {
                    $post->forgetAllCaches();
                };
                Artisan::call('images:removehttp');
                Category::indexRelatedCategoriesTo($event);
            }
        });
    }

    public static function forgetCache($id)
    {
        Cache::tags(["Event:$id"])->flush();
    }


//    protected $appends = ['starts_at_fa', 'ends_at_fa'];

    public static function flushPagesCache()
    {

    }

    public static function storeVisits()
    {
        $event_keys = Redis::keys(config('cache.prefix') . 'laravel:Event:*:TemporaryVisits');
        foreach ($event_keys as $key) {
            $visits = Redis::smembers($key);
            $event_id = explode(':', $key)[2];
            foreach ($visits as $visit) {
                $user_id = explode(':', $visit)[1];
                if (!$user_id || strlen($user_id) < 1) {
                    $user_id = null;
                }
                Visit::create([
                    'visitable_type' => 'event',
                    'visitable_id' => $event_id,
                    'user_id' => $user_id
                ]);
            }
            Redis::del($key);
        }
    }

    public static function deleteDailyVisitsCache()
    {
        $keys = Redis::keys(config('cache.prefix') . 'laravel:Event:*:DailyVisits');
        foreach ($keys as $key) {
            Redis::del($key);
        }
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }

    public function characters()
    {
        return $this->morphToMany('App\Models\Character', 'property', 'character_property');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title
        ];
    }

    public function getStartsAtFaAttribute()
    {
        try {
            return Jalalian::forge($this->starts_at)->format('Y-m-d H:i:s');
        } catch (\Exception $exception) {
        }
    }

    public function getEndsAtFaAttribute()
    {
        return Jalalian::forge($this->ends_at)->format('Y-m-d H:i:s');
    }

    public function getRatingFromCache()
    {
        $avg = $this->ratings()->avg('rate') ?: 0;
    }

    public function ratings()
    {
        return $this->morphMany('App\Models\Rating', 'ratable');
    }

    public function images()
    {
        return $this->morphMany('App\Models\Imagable', 'imagable');
    }

    public function getVisitsFromCache()
    {
        if (!Redis::exists(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits'))
            $this->cacheDailyVisits();

        return Redis::hgetall(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits');
    }

    private function cacheDailyVisits()
    {
        $visits = Visit::where('visitable_id', '=', $this->id)
            ->where('visitable_type', '=', 'event')
            ->selectRaw('COUNT(*) as count, CAST(created_at AS DATE) as date')
            ->groupBy(DB::raw('CAST(created_at as DATE)'))
            ->get();

        foreach ($visits as $visit) {
            Redis::hset(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits', $visit->date, $visit->count);
        }
    }

    public function getVisitsCountFromCache()
    {
        if (!Redis::exists(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits'))
            $this->cacheDailyVisits();

        $values = Redis::hvals('laravel:Event:' . $this->id . ':DailyVisits');
        $count = 0;

        foreach ($values as $value)
            $count += $value;

        return $count;
    }

    public function visits()
    {
        return $this->morphMany('App\Models\Visit', 'visitable');
    }

    public function incrementVisits(Request $request)
    {
        $result = Redis::sadd(config('cache.prefix') . 'laravel:Event:' . $this->id . ':TemporaryVisits', 'user_id:' . Auth::id());

        if (!Redis::exists(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits'))
            $this->cacheDailyVisits();

        if ($result > 0) {
            Redis::hincrby(config('cache.prefix') . 'laravel:Event:' . $this->id . ':DailyVisits', date('Y-m-d'), 1);
        }
    }

    public function posts()
    {
        return $this->morphMany('App\Models\Post', 'postable');
    }

    public function getCities()
    {
        return $this->cities()->get()->pluck('city');
    }

    public function cities()
    {
        return $this->morphMany('App\Models\Citiable', 'citiable');
    }

    public function searchables()
    {
        return $this->morphMany('App\Models\Searchable', 'searchable');
    }

    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    public function bookmarks()
    {
        return $this->morphMany('App\Models\Bookmark', 'bookmarkable');
    }

    public function tags()
    {
        return $this->morphToMany('App\Models\Tag', 'tagable');
    }

    public function photographers()
    {
        return $this->morphToMany('App\Models\Photographer', 'photographerable');
    }

    public function authors()
    {
        return $this->morphToMany('App\Models\Author', 'authorable');
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place');
    }

    public function admin()
    {
        return $this->belongsTo('App\Models\Admin');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\EventType');
    }

    public function scopeNotEnded($query)
    {
        $query->whereDate('ends_at', '>', Carbon::now());
    }

    public function scopeLanguage($query, $langauge_id)
    {
        return $query->where('language_id', $langauge_id);
    }
}
