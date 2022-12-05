<?php

namespace App\Http\Resources;

use App\Models\CommentDetail;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class CommentResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        if ($this->user_id !== null) {
            $user = new UserResource($this->user);
        } else if ($this->admin_id !== null) {
            $user = new CommentDetailResource($this->detail);
        } else {
            $user = null;
        }

        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toDateTimeString(),
            'created_at_fa_h' => $this->created_at_fa_h,
            $this->mergeWhen(Auth::guard('admin')->check(), [
                'id' => $this->id,
                'status_id' => $this->status_id,
                'commentable_type' => $this->commentable_type,
                'commentable' => new CommentableResource($this->commentable)
            ]),
            'content' => $this->content,
            'user' => $user,
            'show_type_id' => $this->detail ? $this->detail->show_type_id : CommentDetail::SHOW_TYPES['USER']
        ];
    }
}
