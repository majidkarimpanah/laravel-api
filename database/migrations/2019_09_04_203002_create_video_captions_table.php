<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoCaptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_captions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('language', 5)->index()->default('en');
            $table->uuid('hash')->unique();
            $table->string('url')->nullable();
            $table->integer('video_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('order')->unsigned()->index()->default(0);
            $table->timestamps();

            $table->unique(['name', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_captions');
    }
}
