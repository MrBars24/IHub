@extends('email.layout.master')

@section('content')
	<table class="email-notification" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;position: relative;margin-bottom: 10px;width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td>
				<h1 class="email-intro__title" style="box-sizing: inherit;margin: 0.8em 0;padding: 0 20px;font-size: 2em;color: #3a3a3a; text-align: left;">Error Log @ {{ date('F j, Y h:i A', strtotime($err->created_at)) }}</h1>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Environment : {{ $err->environment }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Route Name : {{ $err->route_name }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Route Action : {{ $err->route_action }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Request URI : {{ $err->request_uri }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Request Method : {{ $err->request_method }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Response Code : {{ $err->response_code }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Request Input :</p>
				@if(count(json_decode($err->request_input)) > 0)
				<div style="margin:0 30px">
					<table style="display:table;width: 100%;max-width: 100%;margin-bottom: 20px;border-spacing: 0;border-collapse: collapse;">
						<thead>
							<tr>
								<th style="vertical-align: bottom;border-bottom: 2px solid #ddd;padding:8px;line-height: 1.42857143;">Var name</th>
								<th style="vertical-align: bottom;border-bottom: 2px solid #ddd;padding:8px;line-height: 1.42857143;">Value</th>
							</tr>
						</thead>
						<tbody>
						@foreach(json_decode($err->request_input) as $key => $val)
							<tr>
								<td style="padding:8px;line-height:1.42857143;vertical-align: top;border-top: 1px solid #ddd;">{{ $key }}</td>
								<td style="padding:8px;line-height:1.42857143;vertical-align: top;border-top: 1px solid #ddd;">{{ (gettype($val) == 'array' || gettype($val) == 'object') ? json_encode($val) : $val }}</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				@endif
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Auth User : {{ $err->auth_user }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Message : {{ $err->message }}</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">Stack Trace :</p>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 0.5em;text-align: left;">
				{{!! str_replace('\n','<br>',$err->stacktrace) !!}}
				</p>
			</td>
		</tr>
	</table>
@endsection