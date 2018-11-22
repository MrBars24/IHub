<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGigTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gig', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->string('title', 120);
			$table->string('slug', 120);
			$table->string('description', 1024);
			$table->string('filesystem', 24)->nullable();
			$table->text('ideas');
			$table->text('ideas_facebook')->nullable();
			$table->text('ideas_twitter')->nullable();
			$table->text('ideas_linkedin')->nullable();
			$table->text('ideas_pinterest')->nullable();
			$table->text('ideas_youtube')->nullable();
			$table->text('ideas_instagram')->nullable();
			$table->tinyInteger('place_count')->unsigned(); // @todo implement?
			$table->mediumInteger('points')->unsigned();
			$table->text('conditions')->nullable();
			$table->boolean('require_approval')->default(false);
			$table->boolean('is_active')->default(false); // this is manually set with a checkbox in the app
			$table->boolean('is_live')->default(false); // this is set by checking whether the current time is within the start/end dates of the gig
			$table->boolean('has_commenced_notified')->default(false); // if false, this will eventually run in a scheduled task and notify users once the gig commences
			$table->boolean('has_expiring_notified')->default(false); // if false, this will eventually run in a scheduled task and notify users once the gig is expiring
			$table->boolean('has_expired_notified')->default(false); // if false, this will eventually run in a scheduled task and notify users once the gig is expired
			$table->timestamp('commence_at')->nullable();
			$table->timestamp('deadline_at')->nullable();
			$table->timestamps();
			$table->timestamp('deleted_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('gig');
	}
}
