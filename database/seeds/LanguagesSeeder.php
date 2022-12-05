<?php

use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Language::updateOrCreate([
            'title' => 'Farsi',
            'abbreviation' => 'fa'
        ]);

        \App\Models\Language::updateOrCreate([
            'title' => 'English',
            'abbreviation' => 'en'
        ]);

        \App\Models\Language::updateOrCreate([
            'title' => 'Arabic',
            'abbreviation' => 'ar'
        ]);


    }
}
