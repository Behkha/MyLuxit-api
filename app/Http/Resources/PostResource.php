<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use Illuminate\Http\Resources\Json\Resource;

class PostResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'postable_type' => $this->postable_type,
            'postable_id' => $this->postable_id,
            'publish_at' => $this->publish_at->toDateTimeString(),
            'occur_at' => $this->occur_at->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'tags' => $this->tags,
            'subtitle' => $this->subtitle,
            'postable' => new PostableResource($this->whenLoaded('postable')),
            'publish_at_hi' => $this->publish_at_hi,
            'occur_at_hi' => $this->occur_at_hi,
            $this->mergeWhen(isset($this->resource->getRelations()['cities']), [
                'cities' => $this->getCities()
            ]),
        ];
    }
}
