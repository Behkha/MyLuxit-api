<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CelebrityResource extends Resource
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
            'name' => $this->title,
            'jobs' => $this->jobs,
            'bio' => $this->bio,
            'media' => $this->media,
            'contact' => $this->contact,
            'character' => new CharacterResource($this->whenLoaded('character')),
        ];
    }
}
