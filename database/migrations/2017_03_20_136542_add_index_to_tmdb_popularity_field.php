<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexToTmdbPopularityField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('titles', function($table)
		{
    		$table->index('tmdb_popularity');
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