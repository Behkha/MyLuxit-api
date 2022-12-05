<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Place;
use Illuminate\Console\Command;

class RemovehttpFromImagesPath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:removehttp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Place::get()->each(function (Place $place) {
            if (is_array($place->media)) {
                $media = $place->media;
                foreach ($media as $key => $image) {
                    if (!in_array('path', array_keys($image)) || !in_array('preview_path', array_keys($image))) {
                        continue;
                    }
                    $path = $image['path'];
                    $previewPath = $image['preview_path'];

                    if (substr($path, '0', '7') === "https://") {
                        $media[$key]['path'] = str_replace('https://', '', $path);
                    }

                    if (substr($previewPath, '0', '7') === 'https://') {
                        $media[$key]['preview_path'] = str_replace('https://', '', $previewPath);
                    }
                }

                $place->update([
                    'media' => $media
                ]);
            }
        });

        Event::get()->each(function (Event $event) {
            if (is_array($event->media)) {
                $media = $event->media;
                foreach ($media as $key => $image) {
                    if (!in_array('path', array_keys($image)) || !in_array('preview_path', array_keys($image))) {
                        continue;
                    }
                    $path = $image['path'];
                    $previewPath = $image['preview_path'];

                    if (substr($path, '0', '7') === "https://") {
                        $media[$key]['path'] = str_replace('https://', '', $path);
                    }

                    if (substr($previewPath, '0', '7') === 'https://') {
                        $media[$key]['preview_path'] = str_replace('https://', '', $previewPath);
                    }
                }

                $event->update([
                    'media' => $media
                ]);
            }
        });

        $this->info('Successful!');
    }
}
