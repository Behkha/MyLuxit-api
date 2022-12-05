<?php

use Illuminate\Database\Seeder;

class PlaceImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Place::get()->each(function ($place) {
            $place->images()->saveMany(factory(\App\Models\Imagable::class, rand(3, 8))->make());
        });
    }
}
