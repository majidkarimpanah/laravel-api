<?php

use Illuminate\Database\Migrations\Migration;

class AddColumnsToTitles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('titles', function($table)
		{
    		$table->string('language')->nullable();
			$table->string('country')->nullable();
			$table->string('original_title')->nullable();
			$table->string('affiliate_link')->nullable();
			$table->string('custom_field')->nullable();

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
		Schema::table('titles', function($table)
		{
		    $table->dropColumn('language');
		    $table->dropColumn('country');
		    $table->dropColumn('original_title');
		    $table->dropColumn('affiliate_link');
		    $table->dropColumn('custom_field');
		});
	}

}