<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Searchable extends Model
{
    use \Laravel\Scout\Searchable;
    protected $guarded = [];

    public function searchableSource()
    {
        return $this->morphTo('searchable', 'searchable_type', 'searchable_id');
    }


    public function toSearchableArray()
    {
        return [];
        $type = $this->searchable_type;
        $id = $this->searchable_id;

        try {
            if ($type === 'event') {
                $title = Event::getById($id)->title;
            } else if ($type === 'place') {
                $title = Place::getById($id)->name;
            }
        } catch (\Exception $exception) {
            return [];
        }

        $array = [
            'id' => $this->id,
            'title' => $title
        ];
        return $array;
    }
}
