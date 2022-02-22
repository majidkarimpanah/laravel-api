<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePersonTitleColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('creditables', function (Blueprint $table) {
            $table->integer('order')->unsigned()->default(0)->index();
            $table->string('department', 100)->nullable();
            $table->string('job', 100)->nullable();
            $table->string('char_name')->nullable()->default(null)->change();
            $table->string('creditable_type', 50)->nullable()->index();

            $table->dropColumn('known_for');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');

            $table->renameColumn('actor_id', 'person_id');
            $table->renameColumn('title_id', 'creditable_id');
            $table->dropIndex('actor_title_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_title', function (Blueprint $table) {
            //
        });
    }
}
