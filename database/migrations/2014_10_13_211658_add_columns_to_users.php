<?php

use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasColumn('users', 'background')) return;

	    Schema::table('users', function($table)
		{
    		$table->string('background')->nullable();
		});
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$table->dropColumn('background');
	}

}
