<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHubTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hub', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('package_id')->unsigned()->nullable();
			$table->integer('subscription_id')->unsigned()->nullable();
			$table->string('name', 120);
			$table->string('slug', 120);
			$table->string('summary', 1024)->nullable();
			$table->string('email', 190);
			$table->string('profile_picture', 40)->nullable();
			$table->string('cover_picture', 40)->nullable();
			$table->string('profile_picture_cropping', 150)->nullable();
			$table->string('cover_picture_cropping', 150)->nullable();
			$table->string('profile_picture_display', 15)->default('square');
			$table->boolean('is_active')->default(false);
			$table->boolean('is_taggable')->default(true);
			$table->text('community_conditions')->nullable();
			$table->text('default_gig_conditions')->nullable();
			$table->string('branding_header_colour', 7)->default('#3a348c')->nullable();
			$table->string('branding_header_colour_gradient', 7)->default('#d13f26')->nullable(); // to support the gradient colour
			$table->string('branding_header_logo', 120)->nullable();
			$table->string('branding_primary_button', 7)->default('#f5a194')->nullable();
			$table->string('branding_primary_button_text', 7)->default('#ffffff')->nullable();
			$table->string('email_logo', 120)->nullable();
			$table->string('email_header_colour', 7)->default('#20272d')->nullable();
			$table->string('email_footer_colour', 7)->default('#20272d')->nullable();
			$table->string('email_footer_text_1', 7)->default('#ffffff')->nullable();
			$table->string('email_footer_text_2', 7)->default('#5e5e5e')->nullable();
			$table->boolean('default_gig_require_approval')->default(false);
			$table->string('filesystem', 24)->nullable();
			$table->text('custom_fields')->nullable();
			$table->timestamp('conditions_updated_at')->nullable();
			$table->text('email_invite_text')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('hub');
	}
}
