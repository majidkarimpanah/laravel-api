<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviews extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reviews', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('author');
			$table->string('source')->nullable();
			$table->text('body')->nullable();
			$table->integer('score')->nullable();
			$table->string('link')->nullable();
			$table->integer('title_id')->unsigned();
			$table->integer('user_id')->unsigned()->nullable();
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();			
			$table->string('temp_id', 30)->nullable();

			$table->unique(array('title_id','author'), 'author_title_unique');

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
		Schema::drop('reviews');
	}

}
