<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Resources\Json\Resource;

class SearchableResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->searchable_type === 'event') {
            return new EventResource(Event::getById($this->searchable_id));
        } else if ($this->searchable_type === 'place') {
            return new PlaceResource(Place::getById($this->searchable_id));
        }
        return [];
    }
}
