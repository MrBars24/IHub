<?php

namespace App\Http\Controllers\Hub;

// App
use App\Http\Controllers\Controller;
use App\Alert;
use App\AlertGig;
use App\Hub;
use App\Gig;

// Laravel
use Illuminate\Http\Request;

class AlertController extends Controller
{
	/**
	 * GET /{hub}/a/{alert}
	 * ROUTE hub::alert.read [web.php]
	 *
	 * The alert ping end resource. This will log the alert as read and return a dummy image
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Alert                $alert
	 * @return \Illuminate\Http\Response
	 */
	public function getAlert(Request $request, Hub $hub, Alert $alert)
	{
		// params
		$requestHash = $request->input('e');

		// check point: alert valid
		// - alert exists
		if(!$alert->exists) {
			abort(404);
		}

		// get reference hash for alert
		$referenceHash = $alert->generateHash();

		// check point: alert valid
		// - email hash matches against alert hash
		if($referenceHash !== $requestHash) {
			abort(404);
		}

		// set viewed (alert)
		if(is_null($alert->read_at)) {
			$alert->read_at = carbon();
			$alert->save();
		}

		// output dummy image
		$image = public_path('/images/blank.gif');
		$filesize = filesize($image);

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Disposition: attachment; filename="beacon_' . $alert->id  . '.gif"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $filesize);
		readfile($image);
		exit;
	}

	/**
	 * GET /{hub}/a/g/{alert}/{gig}
	 * ROUTE hub::alert.gig [web.php]
	 *
	 * Register click through for gig in alert
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Alert                $alert
	 * @param  \App\gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function getReadAlertGig(Request $request, Hub $hub, Alert $alert, Gig $gig)
	{
		// params
		$requestHash = $request->get('e');

		// check point: alert valid
		// - alert exists
		// - gig exists
		if(!$alert->exists || !$gig->exists) {
			abort(404);
		}

		// get reference hash for alert
		$referenceHash = $alert->generateHash();

		// check point: alert valid
		// - email hash matches against alert hash
		if($referenceHash !== $requestHash) {
			abort(404);
		}

		// get alert gig pivot
		$pivot = AlertGig::query()
			->where('alert_id', '=', $alert->id)
			->where('gig_id', '=', $gig->id)
			->first();

		// check point: alert gig found
		if(is_null($pivot) || !$pivot->exists) {
			abort(404);
		}

		// set viewed (alert)
		if(is_null($alert->read_at)) {
			$alert->read_at = carbon();
			$alert->save();
		}

		// set viewed (gig)
		if(is_null($pivot->viewed_at)) {
			$pivot->viewed_at = carbon();
			$pivot->save();
		}

		// redirect to gig via login route
		$referrer = str_replace('/api/', '/', route('hub::gig.view', [$hub, $gig]));
		return redirect($referrer);
		//return redirect()->route('general::entry', ['r' => $referrer]);
	}
}