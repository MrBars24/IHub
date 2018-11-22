@extends('email.layout.standard', ['hub' => $notification->hub])

@section('content-inner')
	<table class="email-notification" 
		style="box-sizing: inherit;
			margin: 0;
			padding: 0;
			border-collapse: collapse;
			border-spacing: 0;
			display: block;
			position: relative;
			margin-bottom: 10px;
			width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td class="email-notification__sender avatar" width="25%" 
				style="box-sizing: inherit;
					margin: 0;
					padding: 0px 20px;
					border-radius: 0;
					vertical-align: top;">
				<img src="" alt="" width="120" height="120" 
					style="box-sizing: inherit;margin: 0;
						padding: 0;
						max-width: none;
						max-height: none;
						border: 1px solid #e9eced;
						border-radius: 0;
						display: block;
						width: 120px;
						height: 120px;">
			</td>
			<td class="email-notification__details" 
				style="
					box-sizing: inherit;
					margin: 0;
					padding: 0;
					text-align: left;
					padding-right: 20px;">
				<h1 class="email-notification__title" 
					style="box-sizing: inherit;
						margin: 0.67em 0;
						padding: 0;
						font-size: 1.4em;
						color: #000000;
						margin-top: 0.8em;
						margin-bottom: 0.5em;">
					{{ $notification->summary }}
				</h1>
				<p class="email-notification__description" 
					style="box-sizing: inherit;
						margin: 0;
						padding: 0;
						color: #3a3a3a;
						line-height: 1.6;
						margin-top: 0.8em;
						margin-bottom: 1.8em;">
						{{ $notification->message_plain }}
				</p>
				<p style="box-sizing: inherit;margin: 0;padding: 0;">
					<a class="email-notification__view button --small" href="{{ $notification->link }}" 
						style="
							box-sizing: inherit;
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
							background: {{ $notification->hub->branding_primary_button }};
							border: 1px solid {{ $notification->hub->branding_primary_button }};
							color: {{ $notification->hub->branding_primary_button_text }};
							font-size: 1em;">
						View</a>
				</p>
			</td>
		</tr>
	</table>
@endsection