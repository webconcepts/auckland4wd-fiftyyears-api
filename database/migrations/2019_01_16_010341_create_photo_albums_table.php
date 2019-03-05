<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotoAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photo_albums', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->date('date')->nullable();
            $table->integer('approx_day')->nullable();
            $table->integer('approx_month')->nullable();
            $table->integer('approx_year')->nullable();
            $table->string('location')->nullable();
            $table->string('photographer')->nullable();
            $table->text('description')->nullable();
            $table->datetime('published_at')->nullable();
            $table->datetime('removed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photo_albums');
    }
}
