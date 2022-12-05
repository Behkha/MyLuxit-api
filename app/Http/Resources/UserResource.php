<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $needsMoreData = Auth::guard('admin')->check() || Auth::id() === $this->id;

        return [
            $this->mergeWhen($needsMoreData, [
                'id' => $this->id,
                'tell' => $this->tell,
                'email' => $this->email,
                'birth_date' => $this->birth_date,
                'gender' => $this->gender,
                'created_at' => $this->created_at->toDateTimeString(),
                'activation_code' => $this->activation_code,
                'city' => new CityResource($this->whenLoaded('city')),
            ]),
            'name' => $this->name,
            'is_profile_picture_accepted' => boolval($this->is_profile_picture_accepted),
            $this->mergeWhen(Auth::guard('admin')->check() || $this->is_profile_picture_accepted, [
                'avatar' => $this->profile_picture,
            ])
        ];
    }
}
