<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class AdminResource extends Resource
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
            'name' => $this->name,
            'username' => $this->username,
            'privileges' => $this->privileges,
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
