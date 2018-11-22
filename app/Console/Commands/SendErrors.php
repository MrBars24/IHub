<?php

namespace App\Console\Commands;

use App\ErrorLog;

// Laravel
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendErrors extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'errors:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send logged errors emails to support team';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// get error log messages that have not yet been sent
		$err = ErrorLog::where('is_sent', '=', false)->get();

		// go through all errors and send individual emails
		foreach($err as $e) {

			// send the error email out to distribution list
			$data = [
				'err' => $e
			];
			Mail::send('email.logs', $data, function($message) use ($e) {
				// mark email as sent
				$e->is_sent = true;
				$e->sent_at = Carbon::now();
				$e->save();

				// assign recipients based on env
				$emails = explode(',', env('ERROR_LOGS_RECEPIENT'));

				// compile message
				$message
					->from('noreply@influencerhub.com', 'Influencer HUB')
					->to($emails, 'Satoshi Payne')
					->subject('Influencer Hub error : ' . $e->environment . ' "' . $e->message . '"');
			});
		}
	}
}
