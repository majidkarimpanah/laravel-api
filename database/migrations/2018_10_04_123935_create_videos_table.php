<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('thumbnail')->nullable();
            $table->text('url');
            $table->string('type', 50);
            $table->string('quality', 50)->nullable();
            $table->integer('title_id')->unsigned()->index();
            $table->integer('season')->nullable()->unsigned()->index();
            $table->integer('episode')->nullable()->unsigned()->index();
            $table->string('source')->default('local')->index();
            $table->integer('negative_votes')->unsigned()->default(0);
            $table->integer('positive_votes')->unsigned()->default(0);
            $table->integer('reports')->unsigned()->default(0);
            $table->integer('approved')->unsigned()->default(1)->index();
            $table->integer('order')->unsigned()->default(0)->index();
            $table->timestamps();

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
        Schema::dropIfExists('videos');
    }
}
