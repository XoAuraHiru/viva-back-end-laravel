<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieGenreTable extends Migration
{
    public function up()
    {
        Schema::create('movie_genre', function (Blueprint $table) {
            $table->id();
            $table->integer('movie_id');
            $table->integer('genre_id');
            $table->timestamps();

            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movie_genre');
    }
}
