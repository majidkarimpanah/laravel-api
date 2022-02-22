<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('actors_titles', function($table)
		{
    		$table->index('actor_id');
    		$table->index('title_id');
		});
		
		Schema::table('episodes', function($table)
		{
    		$table->index('season_id');
    		$table->index('episode_number');
    		$table->index('season_number');
		});

		Schema::table('seasons', function($table)
		{
    		$table->index('title_id');
    		$table->index('title_tmdb_id');
		});

		Schema::table('reviews', function($table)
		{
    		$table->index('title_id');
		});

		Schema::table('images', function($table)
		{
    		$table->index('title_id');
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