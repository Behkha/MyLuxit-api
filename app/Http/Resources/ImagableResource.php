<?php

namespace App\Http\Resources;

use App\Models\Imagable;
use Illuminate\Http\Resources\Json\Resource;

class ImagableResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        return [
            'id' => $this->id,
            'imagable_type' => $this->imagable_type,
            'imagable_id' => $this->imagable_id,
            'status' => array_search($this->status_id,Imagable::Statuses),
            'media' => $this->media,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
