<?php

use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Celebrity::query()->delete();
        \App\Models\Character::query()->delete();
        \App\Models\CharacterType::query()->delete();
        \App\Models\Comment::where('commentable_type', 'celebrity')->delete();

        factory(\App\Models\Celebrity::class, 10)->create();
        \App\Models\Celebrity::all()->each(function ($celeb) {
            $celeb->character()->create([]);
        });

        $events = \App\Models\Event::all();
        $places = \App\Models\Place::all();
        \App\Models\Character::all()->each(function ($char) use ($events, $places) {
            $char->events()->save($events->random());
        });

        factory(\App\Models\CharacterType::class, 10)->create();
        $types = \App\Models\CharacterType::all();
        \App\Models\Character::all()->each(function ($char) use ($types) {
            $char->types()->saveMany($types->random(rand(1, 4)));
        });

        \App\Models\Celebrity::all()->each(function ($celeb) {
            $celeb->comments()->saveMany(factory(\App\Models\Comment::class, rand(1, 5))->make());
        });
    }
}
