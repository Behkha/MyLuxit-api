<?php

namespace App\Http\Resources;

use App\Models\CharacterType;
use Illuminate\Http\Resources\Json\Resource;

class CharacterResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->character_type === 'celebrity') {
            $personResource = new CelebrityResource($this->whenLoaded('character'));
        }
        return [
            'id' => $this->id,
            'character_type' => $this->character_type,
            'character_type_fa' => $this->character_type_fa,
            'character_id' => $this->character_id,
            'types' => CharacterTypeResource::collection($this->whenLoaded('types')),
            'person' => $personResource
        ];
    }
}
