<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class BookmarkResource extends Resource
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
            'bookmarkable_id' => $this->bookmarkable_id,
            'bookmarkable_type' => $this->bookmarkable_type,
            'bookmarkable' => new BookmarkableResource($this->whenLoaded('bookmarkable'))
        ];
    }
}
