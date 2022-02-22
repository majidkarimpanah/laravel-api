<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrateIndexesToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('titles', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('titles');

            $table->unique(['tmdb_id', 'is_series']);

            if (array_key_exists('titles_imdb_id_unique', $indexesFound)) {
                $table->dropUnique('titles_imdb_id_unique');
            }

            if (array_key_exists('title', $indexesFound)) {
                $table->dropIndex('title');
            }

            if (array_key_exists('titles_title_index', $indexesFound)) {
                $table->dropIndex('titles_title_index');
            }

            if (array_key_exists('created_at', $indexesFound)) {
                $table->dropIndex('created_at');
            }

            if (array_key_exists('type', $indexesFound)) {
                $table->dropUnique('type');
            }
        });

        Schema::table('people', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('people');

            if (DB::table('people')->count() === 0) {
                $table->unique(['tmdb_id']);
            }

            if (array_key_exists('actors_name_unique', $indexesFound)) {
                $table->dropUnique('actors_name_unique');
            }
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
