<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rating', 20);
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('video_id')->unsigned()->index();
            $table->string('user_ip', 20)->index();

            $table->unique(['user_id', 'video_id']);
            $table->unique(['user_ip', 'video_id']);

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('video_ratings');
    }
}
