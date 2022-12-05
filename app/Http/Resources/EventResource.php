<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class EventResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $details = null;
        if (is_array($this->details)) {
            $details = array_merge($this->details, [
                'duration' => [
                    'title' => 'تاریخ شروع و پایان',
                    'type' => 'duration',
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                    'starts_at_fa' => $this->starts_at_fa,
                    'ends_at_fa' => $this->ends_at_fa
                ]
            ]);
        } else {
            $details = [
                'duration' => [
                    'title' => 'تاریخ شروع و پایان',
                    'type' => 'duration',
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                    'starts_at_fa' => $this->starts_at_fa,
                    'ends_at_fa' => $this->ends_at_fa
                ]
            ];
        }


        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'subtitle' => $this->subtitle,
            'media' => $this->media,
            'links' => $this->links,
            'meta' => $this->meta,
            'postable_type' => 'event',
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'place' => new PlaceResource($this->whenLoaded('place')),
            'type' => new EventTypeResource($this->whenLoaded('type')),
            'authors' => new AuthorCollection($this->whenLoaded('authors')),
            'photographers' => new PhotographerCollection($this->whenLoaded('photographers')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'ratings' => round($this->getRatingFromCache()),
            'tags' => new TagCollection($this->whenLoaded('tags')),
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'starts_at_fa' => $this->starts_at_fa,
            'ends_at_fa' => $this->ends_at_fa,
            'characters' => new CharacterCollection($this->whenLoaded('characters')),
            'visits' => $this->when(Auth::guard('admin')->check(), [
                'dates' => $this->getVisitsFromCache(),
                'total' => $this->getVisitsCountFromCache()
            ]),
            'is_bookmarked' => $this->when(Auth::check(), $this->isBookmarkedByUser(Auth::user())),
            'details' => $details,
            $this->mergeWhen(isset($this->resource->getRelations()['cities']), [
                'cities' => $this->getCities()
            ]),
            'language' => new LanguageResource($this->whenLoaded('language'))
        ];
    }

    public function isBookmarkedByUser(User $user = null)
    {
        if ($user === null) {
            return false;
        }

        $is_bookmarked = Redis::sIsMember('User:' . $user->id . ':EventBookmarks', $this->id);
        return (boolean)$is_bookmarked;
    }
}
