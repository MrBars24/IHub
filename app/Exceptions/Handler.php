<?php

namespace App\Exceptions;

// App
use App;
use App\ErrorLog;

// Laravel
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Route;

// 3rd Party
use Exception;
use Carbon\Carbon;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		\Illuminate\Database\Eloquent\ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function render($request, Exception $exception)
	{
		// find recent similar errors:
		// - if found, then we'll just increment the counter value on the error
		// - otherwise, log new error
		$error = ErrorLog::query()
			->where('created_at', '>', Carbon::now()->subMinutes(15))
			->where('message', '=', $exception->getMessage())
			->get();

		// check if auth is exist
		try {
			$auth = auth()->guard('api')->user();
			
			// assign user data if exist
			$user = [
				'name' => $auth->name,
				'email' => $auth->email
			];
		} catch(Exception $e) {
			$user = [];
		}

		try {
			// increment counter if error already exists
			if ($error->count() > 0) {
				$error = $error->first();
				$error->counter++;
				$error->save();
			} // log new error
			else {
				$err = new ErrorLog;
				$err->message = $exception->getMessage();
				$err->exception_classname = get_class($exception);
				$err->environment = app()->environment();
				$err->route_name = Route::currentRouteName();
				$err->route_action = Route::getCurrentRoute()->getActionName();
				$err->request_uri = $request->fullUrl();
				$err->request_method = $request->method();
				$err->request_input = json_encode($request->input());
				$err->response_code = app('Illuminate\Http\Response')->status();
				$err->stacktrace = json_encode($exception->getTraceAsString());
				$err->auth_user = json_encode($user);
				$err->save();
			}
		} catch(Exception $e) {
			echo('Error generating error log!');
			dd($e->getTrace());
		}

		// response
		return parent::render($request, $exception);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Auth\AuthenticationException  $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		if($request->expectsJson()) {
			return response()->json(['error' => 'Unauthenticated.'], 401);
		}

		return redirect()->guest('login');
	}
}
