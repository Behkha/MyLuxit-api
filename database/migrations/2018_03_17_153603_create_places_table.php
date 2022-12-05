<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('content');
            $table->text('address')->nullable();
            $table->string('location')->nullable();
            $table->integer('city_id')->unsigned();
            $table->integer('admin_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->json('media')->nullable();
            $table->json('links')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')->references('id')->on('cities');
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
        Schema::dropIfExists('places');
    }
}
