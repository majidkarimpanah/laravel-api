<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEpisodesTableToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('release_date')->nullable()->change();
            $table->integer('tmdb_vote_count')->unsigned()->nullable();
            $table->decimal('tmdb_vote_average', 3, 1)->nullable();
            $table->decimal('local_vote_average', 3, 1)->nullable();
            $table->smallInteger('year')->unsigned()->nullable();
            $table->integer('popularity')->unsigned()->nullable()->index();
            $table->renameColumn('plot', 'description');
            $table->renameColumn('title', 'name');
            $table->index('title_id');

            if (Schema::hasColumn('episodes', 'promo')) {
                $table->dropColumn('promo');
                $table->dropColumn('temp_id');
            }

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
        //
    }
}
