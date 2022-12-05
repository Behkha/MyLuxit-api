<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $this->createAdmin();
//        $this->call('CitySeeder');
//        $this->createUser();
////        $this->createBookmarkCollections();
//        $this->createPlaceTypes();
        $this->call('PlaceSeeder');
//        $this->createPlaceComments();
//        $this->createEventTypes();
//        $this->call('EventSeeder');
//        $this->createEventComments();
//        $this->createBookmarks();
//        $this->createAuthors();
//        $this->createPhotographers();
//        $this->createTags();
//        $this->createCategories();
//        $this->createPosts();
//        $this->createRatings();
//        $this->createCommentDetails();
////        $this->createReferrals();


        $this->call('CharacterSeeder');
        $this->call('PlaceImageSeeder');
        Cache::flush();
    }

    private function createAdmin()
    {
        \App\Models\Admin::create([
            'name' => 'کیانوش',
            'username' => 'keyanoosh',
            'password' => \Illuminate\Support\Facades\Hash::make('godofgods'),
            'privileges' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
        ]);

        \App\Models\Admin::create([
            'name' => 'میلاد',
            'username' => 'milad',
            'password' => \Illuminate\Support\Facades\Hash::make('milad'),
            'privileges' => []
        ]);

        \App\Models\Admin::create([
            'name' => 'بهزاد',
            'username' => 'behkha',
            'password' => \Illuminate\Support\Facades\Hash::make('behkha'),
            'privileges' => []
        ]);

        \App\Models\Admin::create([
            'name' => 'آقا الف',
            'username' => 'aghaalef',
            'password' => \Illuminate\Support\Facades\Hash::make('aghaalef'),
            'privileges' => []
        ]);
    }

    private function createUser()
    {
        factory(\App\Models\User::class, 10)->create();
    }

    private function createPlaceTypes()
    {
        factory(\App\Models\PlaceType::class, 10)->create();
    }

    private function createPlaceComments()
    {
        \App\Models\Place::all()->each(function ($place) {
            $place->comments()->saveMany(factory(\App\Models\Comment::class, rand(1, 4))->make());
        });
    }

    private function createEventTypes()
    {
        factory(\App\Models\EventType::class, 10)->create();
    }

    private function createEventComments()
    {
        \App\Models\Event::all()->each(function ($event) {
            $event->comments()->saveMany(factory(\App\Models\Comment::class, rand(1, 4))->make());
        });
    }

    private function createBookmarks()
    {
        \App\Models\Place::all()->each(function ($place) {
            $place->bookmarks()->saveMany(factory(\App\Models\Bookmark::class, rand(1, 2))->make());
        });

        \App\Models\Event::all()->each(function ($event) {
            $event->bookmarks()->saveMany(factory(\App\Models\Bookmark::class, rand(1, 2))->make());
        });
    }

    private function createAuthors()
    {
        factory(\App\Models\Author::class, 5)->create();

        \App\Models\Place::all()->each(function ($place) {
            $place->authors()->save(\App\Models\Author::all()->random());
        });

        \App\Models\Event::all()->each(function ($event) {
            $event->authors()->save(\App\Models\Author::all()->random());
        });
    }

    private function createPhotographers()
    {
        factory(\App\Models\Photographer::class, 5)->create();

        \App\Models\Place::all()->each(function ($place) {
            $place->photographers()->save(\App\Models\Photographer::all()->random());
        });

        \App\Models\Event::all()->each(function ($event) {
            $event->photographers()->save(\App\Models\Photographer::all()->random());
        });
    }

    private function createTags()
    {
        factory(\App\Models\Tag::class, 10)->create();

        $tags = \App\Models\Tag::all();

        \App\Models\Place::all()->each(function ($place) use ($tags) {
            $place->tags()->save($tags->random());
        });

        \App\Models\Event::all()->each(function ($event) use ($tags) {
            $event->tags()->save($tags->random());
        });

        \App\Models\Post::all()->each(function ($post) use ($tags) {
            $post->tags()->save($tags->random());
        });
    }

    private function createCategories()
    {
        factory(\App\Models\Category::class, 10)->create();
    }

    private function createPosts()
    {
        $places = \App\Models\Place::all();
        $events = \App\Models\Event::all();

        for ($i = 0; $i < 5; $i++) {
            $e = $events->random();
            $p = $places->random();

            $e->posts()->save(factory(\App\Models\Post::class)->make());
            $p->posts()->save(factory(\App\Models\Post::class)->make());
        }
    }

    private function createRatings()
    {
        $places = \App\Models\Place::all();
        $events = \App\Models\Event::all();

        for ($i = 0; $i < 5; $i++) {
            $e = $events->random();
            $p = $places->random();

            $e->ratings()->saveMany(factory(\App\Models\Rating::class, rand(1, 4))->make());
            $p->ratings()->saveMany(factory(\App\Models\Rating::class, rand(1, 4))->make());
        }
    }

    private function createCommentDetails()
    {
        $admin_comments = \App\Models\Comment::whereNotNull('admin_id')->get();
        $admin_comments->each(function ($comment) {
            $comment->detail()->save(factory(\App\Models\CommentDetail::class)->make());
        });
    }

    private function createBookmarkCollections()
    {
        factory(\App\Models\BookmarkCollection::class, 5)->create();
    }

    private function createReferrals()
    {
        \App\Models\Referral::create([
            'code' => '2525',
            'name' => 'شرکت آب',
            'status_id' => \App\Models\Referral::STATUSES['active'],
        ]);
    }
}
