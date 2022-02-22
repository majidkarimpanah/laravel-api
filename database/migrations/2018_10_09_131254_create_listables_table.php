<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('list_id')->unsigned()->index();
            $table->integer('listable_id')->unsigned()->index();
            $table->string('listable_type', 80)->index();
            $table->integer('order')->unsigned()->default(0)->index();
            $table->timestamp('created_at')->nullable();

            $table->unique(['list_id', 'listable_id', 'listable_type']);

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
        Schema::dropIfExists('listables');
    }
}
