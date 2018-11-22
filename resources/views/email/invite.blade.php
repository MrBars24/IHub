@extends('email.layout.standard', ['hub' => $hub])

@section('content-inner')
	<table class="email-notification" 
		style="box-sizing: inherit;
			margin: 0;
			padding: 0;
			border-collapse: collapse;
			border-spacing: 0;
			position: relative;
			margin-bottom: 10px;
			width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td class="email-notification__details" style="box-sizing: inherit;
					margin: 0;
					padding: 0px 20px;
					text-align: left;
					width: 100%;">
				<h1 class="email-notification__title" 
					style="box-sizing: inherit;
						margin: 0.67em 0;
						padding: 0;
						font-size: 1.4em;
						color: #000000;
						margin-top: 0.8em;
						margin-bottom: 0.5em;
						text-align: center;
					">
					We&rsquo;d love you to join us!
				</h1>
				<p class="email-notification__description" 
					style="box-sizing: inherit;
						margin: 0;padding: 0;
						color: #3a3a3a;line-height: 1.6;
						margin-top: 0.8em;
						margin-bottom: 1.8em;
						text-align: center;
					">{!! enl2br($hub->email_invite_text) !!}
				</p>
			</td>
		</tr>
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td class="email-notification__details" style="box-sizing: inherit;
				margin: 0;
				padding: 0px 20px;
				text-align: center;
				width: 100%;">
				<p style="box-sizing: inherit;margin: 0;padding: 0;">
					<a class="email-notification__view button --small" 
						href="{{ route('hub::account-setup', ['hub' => $hub->slug, 'membership' => $membership->id]) }}" 
						style="box-sizing: inherit;
							margin: 0;
							padding: 7px 20px;
							text-decoration: none;
							-webkit-tap-highlight-color: transparent;
							background-color: transparent;
							cursor: pointer;
							display: inline-block;
							font-weight: 300;
							line-height: 1.5;
							text-align: center;
							border-radius: 4px;
							box-shadow: none;
							transition: background 0.2s, color 0.2s, border 0.2s;
							background: {{ $hub->branding_primary_button }};
							border: 1px solid {{ $hub->branding_primary_button }};
							color: {{ $hub->branding_primary_button_text }};
							font-size: 1em;">Join hub</a>
				</p>
			</td>
		</tr>
	</table>
@endsection