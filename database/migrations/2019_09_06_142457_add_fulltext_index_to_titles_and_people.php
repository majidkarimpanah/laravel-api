<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFulltextIndexToTitlesAndPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `titles` ADD FULLTEXT INDEX `titles_name_fulltext` (`name`)');
        DB::statement('ALTER TABLE `people` ADD FULLTEXT INDEX `people_name_fulltext` (`name`)');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->dropIndex('titles_name_fulltext');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_name_fulltext');
        });
    }
}
