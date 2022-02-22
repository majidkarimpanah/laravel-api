<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocalVoteCountColumnToTitlesAndEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->integer('local_vote_count')->default(0)->unsigned()->index();
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->integer('local_vote_count')->default(0)->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->dropColumn('local_vote_count');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('local_vote_count');
        });
    }
}
