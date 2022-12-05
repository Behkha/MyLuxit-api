<?php

namespace App\Http\Resources;

use App\Models\CommentDetail;
use Illuminate\Http\Resources\Json\Resource;

class CommentDetailResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->show_type_id === CommentDetail::SHOW_TYPES['ADMIN']) {
            $name = 'چارپایه';
        } else {
            $name = $this->name;
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'avatar' => ''
        ];
    }
}
