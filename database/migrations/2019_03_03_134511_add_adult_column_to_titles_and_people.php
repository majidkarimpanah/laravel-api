<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdultColumnToTitlesAndPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('titles', function(Blueprint $table)
        {
            $table->boolean('adult')->default(0);
            $table->index(['is_series', 'adult']);
        });

        Schema::table('people', function(Blueprint $table)
        {
            $table->boolean('adult')->default(0);
            $table->index(['adult', 'popularity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('people', function($table)
        {
            $table->dropColumn('adult');
        });

        Schema::table('titles', function($table)
        {
            $table->dropColumn('adult');
        });
    }
}
