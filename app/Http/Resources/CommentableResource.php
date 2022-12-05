<?php

namespace App\Http\Resources;

use App\Models\Celebrity;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Resources\Json\Resource;

class CommentableResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource instanceof Event)
            $commentable = new EventResource($this->resource);
        else if ($this->resource instanceof Place)
            $commentable = new PlaceResource($this->resource);
        else if ($this->resource instanceof Celebrity)
            $commentable = new CelebrityResource($this->resource);

        return $commentable->resource->toArray();
    }
}
