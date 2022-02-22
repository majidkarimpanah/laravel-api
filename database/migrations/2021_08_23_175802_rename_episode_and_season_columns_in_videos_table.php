<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEpisodeAndSeasonColumnsInVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('videos', 'episode_num')) {
            return;
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->renameColumn('season', 'season_num');
            $table->renameColumn('episode', 'episode_num');
        });
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
