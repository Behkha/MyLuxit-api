<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorables', function (Blueprint $table) {
            $table->integer('author_id');
            $table->integer('authorable_id');
            $table->string('authorable_type');
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authorables');
    }
}
