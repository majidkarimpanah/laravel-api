<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSeasonsToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('fully_scraped');
            $table->dropColumn('temp_id');
            $table->dropColumn('title_imdb_id');
            $table->dropColumn('overview');

            $table->integer('episode_count')->unsgined();
            $table->tinyInteger('fully_synced')->unsigned()->default(0);

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
        Schema::table('seasons', function (Blueprint $table) {
            //
        });
    }
}
