<?php

namespace App\Http\Resources;

use App\Models\Place;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Morilog\Jalali\Jalalian;

class PlaceResource extends Resource
{

    public function toArray($request)
    {
        $details = $this->details;
        if (is_array($details)) {
            foreach ($details as $key => $detail) {
                if ($detail['type'] === 'schedule') {
                    $full_content = $detail['week_days'];
                    $details[$key]['week_days'] = [];
                    $details[$key]['week_days']['en'] = $full_content;
                    $details[$key]['week_days']['fa'] = $this->generateFaWorkHours($full_content);
                    $dayName = Jalalian::now()->format('l');
                    if (array_key_exists($dayName, $details[$key]['week_days']['fa'])) {
                        $details[$key]['today'] = $details[$key]['week_days']['fa'][$dayName];
                    } else {
                        $details[$key]['today'] = 'نامشخص';
                    }
                    break;
                }
            }
        }


        if ($this->address) {
            $details['address'] = [
                'title' => Place::DetailTypes['address'],
                'type' => 'address',
                'content' => $this->address,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'subtitle' => $this->subtitle,
	    'price' => $this->price,
            'location' => $this->location,
            'address' => $this->address,
            'media' => $this->media,
            'links' => $this->links,
            'meta' => $this->meta,
            'postable_type' => 'place',
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'city' => new CityResource($this->whenLoaded('city')),
            'type' => new PlaceTypeResource($this->whenLoaded('type')),
            'authors' => new AuthorCollection($this->whenLoaded('authors')),
            'photographers' => new PhotographerCollection($this->whenLoaded('photographers')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'ratings' => round($this->getRatingFromCache()),
            'tags' => new TagCollection($this->whenLoaded('tags')),
            'visits' => $this->when(Auth::guard('admin')->check(), [
                'dates' => $this->getVisitsFromCache(),
                'total' => $this->getVisitsCountFromCache(),
            ]),
            'is_bookmarked' => $this->when(Auth::check(), $this->isBookmarkedByUser(Auth::user())),
            'events' => EventResource::collection($this->whenLoaded('events')),
            'events_count' => $this->when($this->events_count !== null, $this->events_count),
            'details' => $details,
            'geo_location' => $this->geo_location,
            $this->mergeWhen(isset($this->resource->getRelations()['cities']), [
                'cities' => $this->getCities()
            ]),
            'language' => new LanguageResource($this->whenLoaded('language'))
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */

    private function generateFaWorkHours($weekDays)
    {
        $fa_content = [];
        foreach ($weekDays as $day => $periods) {
            $jalali = Jalalian::fromCarbon(Carbon::parse($day));
            $fa_week_day = $jalali->format('l');
            $content = '';
            foreach ($periods as $period) {
                $start_period = $this->translateDayPeriod($period['start']);
                $start_time = Carbon::parse($period['start'])->format('h:i');

                $end_period = $this->translateDayPeriod($period['end']);
                $end_time = Carbon::parse($period['end'])->format('h:i');
                $content .= 'از ' . $start_time . ' ' . $start_period . ' تا ' . $end_time . ' ' . $end_period . ' ';
            }
            $content = rtrim($content);
            $fa_content[$fa_week_day] = $content;
        }

        return $fa_content;
    }

    private function translateDayPeriod($time)
    {
        $hour = (int)Carbon::parse($time)->format('H');
        if ($hour < 12) {
            return 'صبح';
        } else if ($hour < 14) {
            return 'ظهر';
        } else if ($hour < 18) {
            return 'بعد از ظهر';
        } else {
            return 'شب';
        }
    }

    public function isBookmarkedByUser(User $user = null)
    {
        if ($user === null) {
            return false;
        }

        $is_bookmarked = Redis::sIsMember('User:' . $user->id . ':PlaceBookmarks', $this->id);
        return (boolean)$is_bookmarked;
    }
}
