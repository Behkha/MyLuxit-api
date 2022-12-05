<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->integer('place_id')->unsigned()->nullable();
            $table->integer('admin_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->json('media')->nullable();
            $table->json('links')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('place_id')->references('id')->on('places');
            $table->foreign('admin_id')->references('id')->on('admins');
            $table->foreign('type_id')->references('id')->on('place_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
