<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('uploaded_by_id');
            $table->boolean('uploaded')->default(false);
            $table->integer('number');
            $table->tinyInteger('type')->unsigned();
            $table->string('original_filename');
            $table->text('description')->nullable();
            $table->unsignedInteger('likes')->default(0);
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
        Schema::dropIfExists('photos');
    }
}
