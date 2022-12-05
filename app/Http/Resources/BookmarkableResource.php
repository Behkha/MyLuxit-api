<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Resources\Json\Resource;

class BookmarkableResource extends Resource
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
            return new EventResource($this->resource);
        else if ($this->resource instanceof Place)
            return new PlaceResource($this->resource);
    }
}
