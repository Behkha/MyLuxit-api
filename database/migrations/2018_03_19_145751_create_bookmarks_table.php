<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->integer('collection_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('bookmarkable_id')->index();
            $table->string('bookmarkable_type')->index();

            $table->foreign('collection_id')->references('id')->on('bookmark_collections');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
//            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookmarks');
    }
}
