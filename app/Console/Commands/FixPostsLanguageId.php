<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class FixPostsLanguageId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set posts language according to their postable';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Post::with(['postable'])->chunk(50, function ($posts) {
            foreach ($posts as $post) {
                if (is_null($post->postable)) {
                    $post->delete();
                } else {
                    $post->update([
                        'language_id' => $post->postable->language_id
                    ]);
                }
            }
        });
    }
}
