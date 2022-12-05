<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanguageIdToPostsEventsPlacesCategoriesTable extends Migration
{

    private $tables = [
        'places',
        'events',
        'posts',
        'categories'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $farsiLanguage = \App\Models\Language::getByAbbr('fa');

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) use ($farsiLanguage) {
                $table->unsignedInteger('language_id')->default($farsiLanguage->id);
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('CASCADE');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) use ($farsiLanguage) {
                $table->dropForeign(['langauge_id']);
                $table->dropColumn(['language_id']);
            });
        }
    }
}
