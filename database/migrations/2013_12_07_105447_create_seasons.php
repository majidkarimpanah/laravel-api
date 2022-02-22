<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeasons extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('seasons', function(Blueprint $table)
		{
			$table->BigIncrements('id');
			$table->string('title')->nullable();
			$table->string('release_date')->nullable();
			$table->string('poster')->nullable();
			$table->text('overview')->nullable();
			$table->integer('number')->default(1);
			$table->bigInteger('title_id')->unsigned()->nullable();
			$table->string('title_imdb_id')->nullable();
			$table->bigInteger('title_tmdb_id')->unsigned()->nullable();
			$table->tinyInteger('fully_scraped')->default(0)->unsigned();
			$table->tinyInteger('allow_update')->default(1)->unsigned();
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->string('temp_id', 30)->nullable();

			$table->unique(array('title_id','number'), 'tile_number_unique');

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
		Schema::drop('seasons');
	}

}
