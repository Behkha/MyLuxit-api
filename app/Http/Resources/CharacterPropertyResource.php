<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CharacterPropertyResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        if ($this->property_type === 'event') {
            $propertyObject = new EventResource($this->resource->propertyObject);
        } else if ($this->property_type === 'place') {
            $propertyObject = new PlaceResource($this->resource->propertyObject);
        }


        return [
            'character_id' => $this->character_id,
            'property_type' => $this->property_type,
            'property_id' => $this->property_id,
            'property_object' => $propertyObject
        ];
    }
}
