<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTitlesTableToV2 extends Migration
{
    public function __construct()
    {
        // fix doctrine "enum" column issue
        DB::getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->integer('runtime')->unsigned()->nullable()->change();
            $table->bigInteger('budget')->unsigned()->nullable()->change();
            $table->bigInteger('revenue')->unsigned()->nullable()->change();
            $table->decimal('tmdb_rating', 3, 1)->default(null)->nullable()->change();
            $table->integer('tmdb_vote_count')->unsigned()->nullable();
            $table->integer('tmdb_popularity')->unsigned()->nullable()->change();
            $table->string('certification', 50)->nullable()->index();

            $table->integer('episode_count')->unsigned()->nullable();
            $table->boolean('series_ended')->unsigned()->default(0);
            $table->boolean('is_series')->unsigned()->default(0);
            $table->decimal('local_vote_average', 3, 1)->unsigned()->nullable();

            $table->dropColumn('awards');
            $table->dropColumn('mc_user_score');
            $table->dropColumn('mc_critic_score');
            $table->dropColumn('mc_num_of_votes');
            $table->dropColumn('imdb_rating');
            $table->dropColumn('imdb_votes_num');
            $table->dropColumn('featured');
            $table->dropColumn('now_playing');
            $table->dropColumn('custom_field');
            $table->dropColumn('temp_id');

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });

        Schema::table('titles', function(Blueprint $table) {
            $prefix = DB::getTablePrefix();
            DB::statement("ALTER TABLE {$prefix}titles CHANGE background backdrop varchar(255) NULL");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE plot description text NULL");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE tmdb_rating tmdb_vote_average decimal(3,1) NULL");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE season_number season_count integer unsigned NULL");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE title name varchar(255) NULL");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE fully_scraped fully_synced tinyint unsigned default 0");
            DB::statement("ALTER TABLE {$prefix}titles CHANGE tmdb_popularity popularity integer unsigned null");
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
            //
        });
    }
}
