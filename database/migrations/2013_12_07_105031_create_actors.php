<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActors extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actors', function(Blueprint $table)
		{
			$table->BigIncrements('id');
			$table->string('name');
			$table->text('bio')->nullable();
			$table->string('sex', 10)->nullable();
			$table->string('full_bio_link', 255)->nullable();
			$table->string('birth_date', 255)->nullable();
			$table->string('birth_place', 255)->nullable();
			$table->string('awards', 255)->nullable();
			$table->string('image', 255)->nullable();			
			$table->string('imdb_id', 255)->nullable();
			$table->bigInteger('views')->default(1);
			$table->bigInteger('tmdb_id')->unsigned()->nullable()->unique();
			$table->tinyInteger('fully_scraped')->default(0)->unsigned();
			$table->tinyInteger('allow_update')->default(1)->unsigned();
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
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
		Schema::drop('actors');
	}

}
