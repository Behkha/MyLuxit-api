<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//use Illuminate\Database\Eloquent\SoftDeletes;

class BookmarkCollection extends Model
{
//    use SoftDeletes;
    protected $fillable = [
        'name',
        'image',
        'user_id'
    ];

    public function bookmarkedEvents()
    {
        return $this->morphedByMany('App\Models\Event', 'bookmarkable', 'bookmarks', 'collection_id')->withTimestamps()->with('user_id');
    }

    public function bookmarkedPlaces()
    {
        return $this->morphedByMany('App\Models\Place', 'bookmarkable', 'bookmarks', 'collection_id')->withTimestamps()->with('user_id');
    }

    public function updateImage()
    {
        $bookmark = self::bookmarks()->with('bookmarkable')->orderByDesc('created_at')->first();
        if ($bookmark) {
            $image = null;
            foreach ($bookmark->bookmarkable->media as $file) {
                if ($file['type'] === 'image') {
                    $image = $file;
                    break;
                }
            }
            $this->image = $image['path'];
        } else
            $this->image = null;

        $this->save();
    }

    public function bookmarks()
    {
        return $this->hasMany('App\Models\Bookmark', 'collection_id', 'id');
    }
}
