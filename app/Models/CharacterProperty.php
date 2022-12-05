<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterProperty extends Model
{
    protected $table = 'character_property';

    public function propertyObject()
    {
        return $this->morphTo('propertyObject', 'property_type', 'property_id');
    }

}
