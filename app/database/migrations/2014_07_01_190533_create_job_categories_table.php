<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('job_categories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
	        $table->string('sort_order');
	        $table->string('cat_status')->default('close');
	        $table->text('content')->nullable();
	        $table->string('thumbnails')->nullable();
	        $table->timestamp('deleted_at')->nullable();
	        $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('job_categories');
	}

}
