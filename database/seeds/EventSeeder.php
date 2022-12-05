<?php

use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 3;
        factory(\App\Models\Event::class, $count)->create();
        foreach (\App\Models\Event::orderBy('id', 'desc')->limit($count)->get() as $event) {
            $event->posts()->save(factory(\App\Models\Post::class)->make());
        }
    }
}
