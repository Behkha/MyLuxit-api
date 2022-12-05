<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentDetail extends Model
{
    const SHOW_TYPES = [
        'ADMIN' => 1,
        'USER' => 2
    ];
    protected $guarded = [];

    public function comment()
    {
        return $this->belongsTo('App\Models\Comment', 'comment_id');
    }
}
