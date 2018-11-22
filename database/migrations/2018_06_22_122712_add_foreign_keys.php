<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeys extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// query for existing foreign keys in the database
		// this will be used as a lookup to check if a foreign key exists before creating it
		// this method relies on knowing what the foreign key name is in the database
		$foreign_keys = DB::table('information_schema.table_constraints')
			->select('CONSTRAINT_NAME')
			->where('TABLE_SCHEMA', '=', env('DB_DATABASE'))
			->where('CONSTRAINT_TYPE', '=', 'FOREIGN KEY')
			->get();
		$foreign_keys = $foreign_keys->pluck('CONSTRAINT_NAME', 'CONSTRAINT_NAME');

		Schema::table('alert_gig', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['alert_gig_gig_id_foreign'])) {
				$table->foreign('gig_id')->references('id')->on('gig');
			}
			if(!isset($foreign_keys['alert_gig_alert_id_foreign'])) {
				$table->foreign('alert_id')->references('id')->on('alert');
			}
		});

		Schema::table('comment', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['comment_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['comment_post_id_foreign'])) {
				$table->foreign('post_id')->references('id')->on('post');
			}
		});

		Schema::table('conversation', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['conversation_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
		});

		Schema::table('gig', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['gig_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
		});

		Schema::table('gig_attachment', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['gig_attachment_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['gig_attachment_gig_id_foreign'])) {
				$table->foreign('gig_id')->references('id')->on('gig');
			}
		});

		Schema::table('gig_category', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['gig_category_gig_id_foreign'])) {
				$table->foreign('gig_id')->references('id')->on('gig');
			}
			if(!isset($foreign_keys['gig_category_category_id_foreign'])) {
				$table->foreign('category_id')->references('id')->on('category');
			}
		});

		Schema::table('gig_feed_post', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['gig_feed_post_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
		});

		Schema::table('linked_account', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['linked_account_user_id_foreign'])) {
				$table->foreign('user_id')->references('id')->on('user');
			}
		});

		Schema::table('membership', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['membership_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['membership_user_id_foreign'])) {
				$table->foreign('user_id')->references('id')->on('user');
			}
		});

		Schema::table('membership_group', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['membership_group_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
		});

		Schema::table('membership_membership_group', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['membership_membership_group_membership_id_foreign'])) {
				$table->foreign('membership_id')->references('id')->on('membership');
			}
			if(!isset($foreign_keys['membership_membership_group_group_id_foreign'])) {
				$table->foreign('group_id')->references('id')->on('membership_group');
			}
		});

		Schema::table('message', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['message_conversation_id_foreign'])) {
				$table->foreign('conversation_id')->references('id')->on('conversation');
			}
		});

		Schema::table('notification', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['notification_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['notification_type_id_foreign'])) {
				$table->foreign('type_id')->references('id')->on('notification_type');
			}
		});

		Schema::table('post', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['post_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
		});

		Schema::table('post_attachment', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['post_attachment_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['post_attachment_post_id_foreign'])) {
				$table->foreign('post_id')->references('id')->on('post');
			}
		});

		Schema::table('post_dispatch_queue', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['post_dispatch_queue_hub_id_foreign'])) {
				$table->foreign('hub_id')->references('id')->on('hub');
			}
			if(!isset($foreign_keys['post_dispatch_queue_job_id_foreign'])) {
				$table->foreign('job_id')->references('id')->on('post_dispatch_job');
			}
			if(!isset($foreign_keys['post_dispatch_queue_post_id_foreign'])) {
				$table->foreign('post_id')->references('id')->on('post');
			}
			if(!isset($foreign_keys['post_dispatch_queue_attachment_id_foreign'])) {
				$table->foreign('attachment_id')->references('id')->on('post_attachment');
			}
			if(!isset($foreign_keys['post_dispatch_queue_user_id_foreign'])) {
				$table->foreign('user_id')->references('id')->on('user');
			}
		});

		Schema::table('push_notification_queue', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['push_notification_queue_notification_id_foreign'])) {
				$table->foreign('notification_id')->references('id')->on('notification');
			}
			if(!isset($foreign_keys['push_notification_queue_user_id_foreign'])) {
				$table->foreign('user_id')->references('id')->on('user');
			}
		});

		Schema::table('reward', function (Blueprint $table) use ($foreign_keys) {
			if(!isset($foreign_keys['reward_gig_id_foreign'])) {
				$table->foreign('gig_id')->references('id')->on('gig');
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('alert_gig', function (Blueprint $table) {
			$table->dropForeign(['gig_id']);
			$table->dropForeign(['alert_id']);
		});

		Schema::table('comment', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['post_id']);
		});

		Schema::table('conversation', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
		});

		Schema::table('gig', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
		});

		Schema::table('gig_attachment', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['gig_id']);
		});

		Schema::table('gig_category', function (Blueprint $table) {
			$table->dropForeign(['gig_id']);
			$table->dropForeign(['category_id']);
		});

		Schema::table('gig_feed_post', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
		});

		Schema::table('linked_account', function (Blueprint $table) {
			$table->dropForeign(['user_id']);
		});

		Schema::table('membership', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['user_id']);
		});

		Schema::table('membership_group', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
		});

		Schema::table('membership_membership_group', function (Blueprint $table) {
			$table->dropForeign(['membership_id']);
			$table->dropForeign(['group_id']);
		});

		Schema::table('message', function (Blueprint $table) {
			$table->dropForeign(['conversation_id']);
		});

		Schema::table('notification', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['type_id']);
		});

		Schema::table('post', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
		});

		Schema::table('post_attachment', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['post_id']);
		});

		Schema::table('post_dispatch_queue', function (Blueprint $table) {
			$table->dropForeign(['hub_id']);
			$table->dropForeign(['job_id']);
			$table->dropForeign(['post_id']);
			$table->dropForeign(['attachment_id']);
			$table->dropForeign(['user_id']);
		});

		Schema::table('push_notification_queue', function (Blueprint $table) {
			$table->dropForeign(['notification_id']);
			$table->dropForeign(['user_id']);
		});

		Schema::table('reward', function (Blueprint $table) {
			$table->dropForeign(['gig_id']);
		});
	}
}
