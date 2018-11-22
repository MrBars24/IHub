<?php // @charset UTF-8

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabasePurger extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$tables = [
			'alert',
			'alert_category_setting',
			'alert_cycle',
			'alert_gig',
			'alert_platform_setting',
			'category',
			'comment',
			'conversation',
			'error_log',
			'facebook_page_access',
			'file_storage',
			'gig',
			'gig_attachment',
			'gig_category',
			'gig_feed',
			'gig_feed_post',
			'gig_ignore',
			'gig_platform',
			'gig_post',
			'hub',
			'like',
			'linked_account',
			'linkedin_company_access',
			'login_history',
			'membership',
			'message',
			'notification',
			'notification_setting',
			'notification_type',
			'oauth_clients',
			'password_reset',
			'pinterest_board_access',
			'platform',
			'point_accrual',
			'point_reset',
			'post',
			'post_attachment',
			'post_dispatch_job',
			'post_dispatch_queue',
			'push_notification_queue',
			'reward',
			'scraped_url',
			'user',

			// leave the following tables commented out; these will be useful to retain
			//'api_paging',
			//'facebook_connection',
			//'instagram_connection',
			//'twitter_connection',
			//'youtube_category',
		];
		foreach($tables as $table) {
			Schema::disableForeignKeyConstraints();
			DB::table($table)->delete();
			DB::statement('ALTER TABLE `' . $table . '` AUTO_INCREMENT = 1');
			Schema::enableForeignKeyConstraints();

		}
	}
}