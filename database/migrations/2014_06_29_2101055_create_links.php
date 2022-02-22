<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('links', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->string('url');
			$table->string('type')->default('embed');
			$table->string('label')->nullable();
			$table->bigInteger('title_id')->unsigned()->nullable();
			$table->integer('season')->unsigned()->nullable();
			$table->integer('episode')->unsigned()->nullable();
			$table->integer('reports')->unsigned()->default(0);
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
			$table->string('temp_id', 30)->nullable();

			$table->unique('url');

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
		Schema::drop('links');
	}

}
