<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
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
            'image' => $this->image,
            'name' => $this->name,
            'position' => $this->position,
            'average_ratings' => $this->when(Auth::guard('admin')->check(), $this->getAverageRatingFromCache()),
            'visits' => $this->when(Auth::guard('admin')->check(), [
                'dates' => $this->getVisitsFromCache(),
                'total' => $this->getVisitsCountFromCache()
            ]),
            'language' => new LanguageResource($this->whenLoaded('language')),
            'has_subcategories' => $this->subcategories()->exists()
        ];
    }
}
