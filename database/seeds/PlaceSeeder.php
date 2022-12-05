<?php

use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 3;
        factory(\App\Models\Place::class, $count)->create();
        foreach (\App\Models\Place::orderBy('id', 'desc')->limit($count)->get() as $place) {
            $place->posts()->save(factory(\App\Models\Post::class)->make());
        }
    }
}
