<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePeopleTableToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->renameColumn('sex', 'gender');
            $table->renameColumn('bio', 'description');
            $table->renameColumn('image', 'poster');

            $table->dropColumn('awards');
            $table->dropColumn('fully_scraped');
            $table->dropColumn('temp_id');
            $table->dropColumn('full_bio_link');

            $table->boolean('fully_synced');
            $table->string('known_for', 50)->nullable();
            $table->integer('popularity')->default(0)->index();
            $table->string('death_date')->nullable();

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
        Schema::table('people', function (Blueprint $table) {
            $table->renameColumn('gender', 'sex');
            $table->renameColumn('description', 'bio');
            $table->renameColumn('poster', 'image');
        });
    }
}
