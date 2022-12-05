<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotographerablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photographerables', function (Blueprint $table) {
            $table->integer('photographer_id');
            $table->integer('photographerable_id');
            $table->string('photographerable_type');
            $table->timestamps();

            $table->foreign('photographer_id')->references('id')->on('photographers')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photographerables');
    }
}
