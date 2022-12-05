<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/
$faker = Faker\Factory::create('fa_IR');

// User
$factory->define(App\Models\User::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'tell' => $faker->phoneNumber,
        'password' => \Illuminate\Support\Facades\Hash::make('12345'),
    ];
});

// Place Type
$factory->define(\App\Models\PlaceType::class, function () use ($faker) {
    return [
        'name' => $faker->realText(30)
    ];
});

// Place
$factory->define(\App\Models\Place::class, function () use ($faker) {
    $media = [
        [
            "type" => "image",
            "name" => "IMG_20181210_214356_256.jpg",
            "path" => "file.chaarpaye.ir/places/default/DN8UiWaPHcEKRtx5WnXHVc8yl2Hj6dJY7MiU50BDxW4SweQljpeg.jpg",
            "preview_path" => "file.chaarpaye.ir/places/preview/DN8UiWaPHcEKRtx5WnXHVc8yl2Hj6dJY7MiU50BDxW4SweQljpeg.jpg"
        ],
        [
            "type" => "image",
            "name" => "IMG_20181210_214358_209.jpg",
            "path" => "file.chaarpaye.ir/places/default/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg",
            "preview_path" => "file.chaarpaye.ir/places/preview/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg"
        ]
    ];


    $location = $faker->latitude . ',' . $faker->longitude;
    $la_lo = explode(',', $location);
    $geo = $la_lo[1] . ' ' . $la_lo[0];
    $first_line = $faker->realText(50);

    return [
        'name' => $faker->realText(40),
        'content' => $faker->realText(200),
        'address' => $faker->address,
        'location' => $location,
        'geo_location' => \Illuminate\Support\Facades\DB::raw("st_GeomFromText('POINT(" . $geo . ")', 4326)"),
        'city_id' => \App\Models\City::all()->random()->id,
        'admin_id' => \App\Models\Admin::all()->random()->id,
        'type_id' => \App\Models\PlaceType::all()->random()->id,
        'media' => $media,
        'links' => [],
        'meta' => [],
        'details' => [
            'tell' => [
                'title' => 'شماره تلفن',
                'type' => 'tell',
                'content' => $faker->phoneNumber,
            ],
            'schedule' => [
                'title' => 'ساعت کاری',
                'type' => 'schedule',
                'week_days' => [
                    'saturday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ], 'sunday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ], 'monday' => [

                    ], 'tuesday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ], 'wednesday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ], 'thursday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ], 'friday' => [
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time(),
                        ],
                        [
                            'start' => $faker->time(),
                            'end' => $faker->time()
                        ]
                    ]
                ]
            ]
        ]
    ];
});

// Event Type
$factory->define(\App\Models\EventType::class, function () use ($faker) {
    return [
        'name' => $faker->realText(30)
    ];
});

// Event
$factory->define(\App\Models\Event::class, function () use ($faker) {
    $media = [
        [
            "type" => "image",
            "name" => "photo_2018-03-19_12-52-22.jpg",
            "path" => "file.chaarpaye.ir/places/default/wJ4gLJh0opQaSSJXUOmgsSCRF2DpBEAD5jopvY8b1aenPLrnjpeg.jpg",
        ],
        [
            "type" => "image",
            "name" => "photo_2018-03-19_12-52-29.jpg",
            "path" => "file.chaarpaye.ir/places/default/qwOpI60h0ht5Y2H1aIqVUvUFSBO2rlzzRUX1tnLPA6oV24Shjpeg.jpg",
        ],
        [
            "type" => "image",
            "name" => "photo_2018-03-19_12-52-34.jpg",
            "path" => "file.chaarpaye.ir/places/default/mTnSkr71647UwC3UBSJHJBWQmGUtQTunj6vi7jmuSTcr5PFdjpeg.jpg",
        ],
    ];

    $first_line = $faker->realText(50);
    if (rand(1, 100) % 5 == 1) {
        $place_id = null;
    } else {
        $place_id = \App\Models\Place::all()->random()->id;
    }

    return [
        'title' => $faker->realText(50),
        'content' => $faker->realText(200),
        'place_id' => $place_id,
        'admin_id' => \App\Models\Admin::all()->random()->id,
        'type_id' => \App\Models\EventType::all()->random()->id,
        'starts_at' => $faker->dateTimeBetween('+10 days', '+15 days'),
        'ends_at' => $faker->dateTimeBetween('+30 days', '45 days'),
        'media' => $media,
        'links' => [],
        'meta' => [],
        'details' => [
            'address' => [
                'title' => 'آدرس',
                'type' => 'address',
                'content' => $faker->address,
            ],
            'tell' => [
                'title' => 'شماره تلفن',
                'type' => 'tell',
                'content' => $faker->phoneNumber,
            ],
            'price' => [
                'title' => 'قیمت بلیط',
                'type' => 'price',
                'content' => $faker->numberBetween(1000, 50000),
            ]
        ]
    ];
});

// Comment
$factory->define(\App\Models\Comment::class, function () use ($faker) {
    $users = \App\Models\User::all();
    $isAdmin = (rand(1, 100) % 5) == 0;
    if ($users->count() && !$isAdmin) {
        $user_id = $users->random()->id;
        $admin_id = null;
    } else {
        $user_id = null;
        $admin_id = \App\Models\Admin::all()->random()->id;
    }
    return [
        'status_id' => $faker->randomElement(\App\Models\Comment::STATUSES),
        'content' => $faker->realText(200),
        'user_id' => $user_id,
        'admin_id' => $admin_id
    ];
});

// Comment Detail
$factory->define(\App\Models\CommentDetail::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'show_type_id' => $faker->randomElement(\App\Models\CommentDetail::SHOW_TYPES)
    ];
});

// Bookmark Collection
$factory->define(\App\Models\BookmarkCollection::class, function () use ($faker) {
    return [
        'name' => $faker->realText(30),
        'image' => '',
        'user_id' => \App\Models\User::all()->random()->id,
    ];
});

// Bookmark
$factory->define(\App\Models\Bookmark::class, function () use ($faker) {
    return [
        'user_id' => \App\Models\User::all()->random()->id,
    ];
});

// Author
$factory->define(\App\Models\Author::class, function () use ($faker) {
    return [
        'name' => $faker->name,
    ];
});

// Photographer
$factory->define(\App\Models\Photographer::class, function () use ($faker) {
    return [
        'name' => $faker->name
    ];
});

// Tag
$factory->define(\App\Models\Tag::class, function () use ($faker) {
    return [
        'name' => $faker->realText(20)
    ];
});

// Category
$factory->define(\App\Models\Category::class, function () use ($faker) {

    $event_filters = [];
    $event_types = [];
    $event_type_ids = \App\Models\Event::pluck('type_id')->toArray();

    foreach (array_rand($event_type_ids, rand(2, 5)) as $index) {
        array_push($event_types, $event_type_ids[$index]);
    }

    $event_tags = [];
    $event_tag_ids = \App\Models\Tag::pluck('id')->toArray();

    foreach (array_rand($event_tag_ids, rand(2, 5)) as $index) {
        array_push($event_tags, $event_tag_ids[$index]);
    }

    $event_filters['types'] = $event_types;
    $event_filters['tags'] = $event_tags;

    $place_filters = [];
    $place_types = [];
    $place_type_ids = \App\Models\Place::pluck('type_id')->toArray();

    foreach (array_rand($place_type_ids, rand(2, 5)) as $index) {
        array_push($place_types, $place_type_ids[$index]);
    }

    $place_tags = [];
    $place_tag_ids = \App\Models\Tag::pluck('id')->toArray();

    foreach (array_rand($place_tag_ids, rand(2, 5)) as $index) {
        array_push($place_tags, $place_tag_ids[$index]);
    }

    $place_filters['types'] = $place_types;
    $place_filters['tags'] = $place_tags;

    return [
        'name' => $faker->realText(50),
        'image' => '',
        'position' => $faker->unique()->numberBetween(0, 9),
        'tags' => [\App\Models\Tag::firstOrFail()->id],
        'event_filters' => $event_filters,
        'place_filters' => $place_filters,
    ];
});

// Post
$factory->define(\App\Models\Post::class, function () use ($faker) {
    return [
        'publish_at' => $faker->dateTimeInInterval('-20 days', '+30 days'),
        'occur_at' => $faker->dateTimeInInterval('now', '+5 days'),
        'admin_id' => \App\Models\Admin::all()->random()->id,
    ];
});

// Ratings
$factory->define(\App\Models\Rating::class, function () use ($faker) {
    return [
        'user_id' => \App\Models\User::all()->random()->id,
        'rate' => rand(0, 10)
    ];
});

// Celebrity
$factory->define(\App\Models\Celebrity::class, function () use ($faker) {
    $media = [
        [
            "type" => "image",
            "name" => "IMG_20181210_214356_256.jpg",
            "path" => "file.chaarpaye.ir/places/default/DN8UiWaPHcEKRtx5WnXHVc8yl2Hj6dJY7MiU50BDxW4SweQljpeg.jpg",
            "preview_path" => "file.chaarpaye.ir/places/preview/DN8UiWaPHcEKRtx5WnXHVc8yl2Hj6dJY7MiU50BDxW4SweQljpeg.jpg"
        ],
        [
            "type" => "image",
            "name" => "IMG_20181210_214358_209.jpg",
            "path" => "file.chaarpaye.ir/places/default/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg",
            "preview_path" => "file.chaarpaye.ir/places/preview/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg"
        ]
    ];

    return [
        'title' => $faker->name,
        'bio' => $faker->realText(300),
        'media' => $media,
        'contact' => [
            'tell' => [
                'title' => 'شماره تلفن',
                'type' => 'tell',
                'content' => $faker->phoneNumber,
            ],
            'email' => [
                'title' => 'ایمیل',
                'type' => 'email',
                'content' => $faker->email
            ]
        ]
    ];
});

$factory->define(\App\Models\CharacterType::class, function () use ($faker) {
    return [
        'title' => $faker->title
    ];
});

$factory->define(\App\Models\Imagable::class, function () use ($faker) {
    $media = [
        "type" => "image",
        "path" => "file.chaarpaye.ir/places/default/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg",
        "preview_path" => "file.chaarpaye.ir/places/preview/zHTPUqGv7tZ6r71dByt0R0gxyubkd6iXUosljgaP3yolX5RLjpeg.jpg"
    ];

    return [
        'user_id' => \App\Models\User::get()->random()->id,
        'status_id' => $faker->randomElement(\App\Models\Imagable::Statuses),
        'media' => $media
    ];
});