<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('character_type', function (Blueprint $table) {
            $table->unsignedInteger('character_id');
            $table->unsignedInteger('type_id');

            $table->foreign('character_id')->references('id')->on('characters')->onDelete('CASCADE');
            $table->foreign('type_id')->references('id')->on('character_types')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('character_type');
    }
}
