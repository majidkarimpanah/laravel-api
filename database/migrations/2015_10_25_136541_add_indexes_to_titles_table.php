<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexesToTitlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('titles', function($table)
		{
    		$table->index('created_at');
    		$table->index('release_date');
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