<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropLegacyV1Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('social');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('categorizables');
        Schema::dropIfExists('group_activity');
        Schema::dropIfExists('options');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('slides');
        Schema::dropIfExists('users_groups');
        Schema::dropIfExists('users_titles');
        Schema::dropIfExists('users_activity');
        Schema::dropIfExists('writers_titles');
        Schema::dropIfExists('writers');
        Schema::dropIfExists('directors_titles');
        Schema::dropIfExists('directors');
        Schema::dropIfExists('news');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
