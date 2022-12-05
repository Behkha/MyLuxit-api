<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Laravel\Scout\Searchable;
use Morilog\Jalali\Jalalian;
use TeamTNT\TNTSearch\TNTSearch;
use TeamTNT\TNTSearch\Indexer\TNTIndexer;


class Post extends Model
{
    use Searchable, SoftDeletes;

    const CacheItemPeriod = 10;
    const CachePagePeriod = 5;
    const CacheThisWeekPeriod = 5;
    protected $guarded = [];
    protected $dates = ['publish_at', 'occur_at'];

    public static function getThisWeekPosts()
    {
        $postsByDays = Cache::remember('Posts:ThisWeek', self::CacheThisWeekPeriod, function () {
            $postsByDays = self::getThisWeekDaysArrays();
            $postsByDays['weekDays'] = self::getThisWeekDays();
            $posts = Post::
            with(['tags', 'postable'])->
            where('publish_at', '<', Carbon::now())->
            where('occur_at', '>', Carbon::today())->
            where('occur_at', '<', Carbon::today()->addDays(7))->
            orderBy('occur_at')->
            get();

            foreach ($posts as $post) {
                $post->updateReferenceObject();
                array_push($postsByDays[Jalalian::forge($post->occur_at)->format("l")], $post);
            }

            return $postsByDays;
        });
        return $postsByDays;
    }

    private static function getThisWeekDaysArrays()
    {
        $postsByDays = [];
        $postsByDays = [];
        for ($i = 0; $i <= 6; $i++) {
            $postsByDays[Jalalian::forge(Carbon::today()->addDays($i))->format("l")] = [];
        }
        return $postsByDays;
    }

    private static function getThisWeekDays()
    {
        $days = [];
        for ($i = 0; $i <= 6; $i++) {
            $days[$i + 1] = Jalalian::forge(Carbon::today()->addDays($i))->format("l");
        }
        return $days;
    }

    public function updateReferenceObject()
    {
        if ($this->postable_type === "event")
            $this->post = Event::getById($this->postable_id);
        else if ($this->postable_type === "place")
            $this->post = Place::getById($this->postable_id);
    }

    public static function flushThisWeekCache()
    {

    }

    public static function boot()
    {
        parent::boot();

        self::created(function ($post) {
            self::flushPostsIndex();
        });

        self::updated(function ($post) {
            $post->forgetAllCaches();
        });

        self::deleted(function ($post) {
            $post->forgetAllCaches();
        });
    }

    public static function flushPostsIndex()
    {
        Cache::tags(['Posts'])->flush();
    }

    public static function getById($id)
    {
        $post = self::with(['postable', 'tags', 'postable.comments', 'cities', 'cities.city'])->findOrFail($id);

        return $post;
    }

    public function getCities()
    {
        return $this->cities()->get()->pluck('city');
    }

    public function cities()
    {
        return $this->morphMany('App\Models\Citiable', 'citiable');
    }

    public function forgetAllCaches()
    {

    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function tags()
    {
        return $this->morphToMany('App\Models\Tag', 'tagable');
    }

    public function admin()
    {
        return $this->belongsTo('App\Models\Admin');
    }

    public function toSearchableArray()
    {
        $type = $this->postable_type;
        $id = $this->postable_id;

        try {
            if ($type === 'event') {
                $title = Event::getById($id)->title;
            } else if ($type === 'place') {
                $title = Place::getById($id)->name;
            }
        } catch (\Exception $exception) {
            return [];
        }

        $array = [
            'id' => $this->id,
            'title' => $title
        ];
        return $array;
    }

    public function getPublishAtHIAttribute()
    {
        if ($this->publish_at) {
            return $this->publish_at->format('Y-m-d H:i');
        }
    }

    public function getOccurAtHIAttribute()
    {
        if ($this->occur_at) {
            return $this->occur_at->format('Y-m-d H:i');
        }
    }

    public function scopeLanguage($query, $langauge_id)
    {
        return $query->where('language_id', $langauge_id);
    }
}
