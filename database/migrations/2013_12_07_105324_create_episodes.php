<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('episodes', function(Blueprint $table)
		{
			$table->BigIncrements('id');
			$table->string('title', 255);
			$table->text('plot')->nullable();
			$table->string('poster', 255)->nullable();
			$table->string('release_date', 255)->nullable();			
			$table->bigInteger('title_id')->unsigned();
			$table->bigInteger('season_id')->unsigned();
			$table->integer('season_number')->default(1)->unsigned();
			$table->integer('episode_number')->default(1)->unsigned();
			$table->tinyInteger('allow_update')->default(1)->unsigned();
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->string('temp_id', 30)->nullable();

			$table->unique(array('episode_number', 'season_number', 'title_id'), 'ep_s_title_unique');

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
		Schema::drop('episodes');
	}

}
