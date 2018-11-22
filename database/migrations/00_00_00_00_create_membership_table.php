<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('membership', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('role', 10); // 'influencer', 'hubmanager', 'master'
			$table->string('status', 10); // 'member', 'pending', 'suspended', 'booted'
			$table->mediumInteger('points')->unsigned()->default(0);
			$table->boolean('is_active')->default(false);
			$table->boolean('send_alerts')->default(true);
			$table->string('alert_frequency', 10)->default('day'); // 'fortnight', 'week', 'halfweek', 'day'
			$table->text('custom_fields')->nullable();
			$table->timestamp('joined_at')->nullable();
			$table->timestamp('booted_at')->nullable();
			$table->softDeletes();
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
		Schema::dropIfExists('membership');
	}
}
