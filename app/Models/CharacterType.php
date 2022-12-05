<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterType extends Model
{
    const Types = [
        'celebrity',
    ];
    protected $guarded = [];
}
