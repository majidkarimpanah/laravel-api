<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTitles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('titles', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->string('title')->nullable();
			$table->string('type', 15)->default('movie');
			$table->string('imdb_rating', 10)->nullable();
			$table->decimal('tmdb_rating', 3, 1)->nullable();
			$table->string('mc_user_score', 10)->nullable();
			$table->smallInteger('mc_critic_score')->nullable()->unsigned();
			$table->integer('mc_num_of_votes')->nullable()->unsigned();
			$table->bigInteger('imdb_votes_num')->nullable()->unsigned();
			$table->string('release_date', 25)->nullable();
			$table->smallInteger('year')->nullable()->unsigned();
			$table->text('plot')->nullable();
			$table->string('genre')->nullable();
			$table->string('tagline')->nullable();
			$table->string('poster')->nullable();
			$table->string('background')->nullable();
			$table->string('awards')->nullable();
			$table->string('runtime')->nullable();
			$table->string('trailer')->nullable();
			$table->string('budget')->nullable();
			$table->string('revenue')->nullable()->index();
			$table->bigInteger('views')->default(1);
			$table->integer('tmdb_popularity')->unsigned()->nullable();
			$table->string('imdb_id')->nullable();
			$table->bigInteger('tmdb_id')->unsigned()->nullable();
			$table->tinyInteger('season_number')->nullable()->unsigned();
			$table->tinyInteger('fully_scraped')->default(0)->unsigned();
			$table->tinyInteger('allow_update')->default(1)->unsigned();
			$table->tinyInteger('featured')->default(0)->unsigned();
			$table->tinyInteger('now_playing')->default(0)->unsigned();
			$table->timestamps();
			$table->string('temp_id', 30)->nullable();

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
            $table->engine = 'InnoDB';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('titles');
	}

}
