@extends('email.layout.standard')

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
			<td class=""
			    style="
					box-sizing: inherit;
					margin: 0;
					padding: 0;
					text-align: center;
					width: 100%;" width="100%">
				<h1 class=""
				    style="box-sizing: inherit;
						margin: 0.67em 0;
						padding: 0;
						font-size: 1.4em;
						color: #000000;
						margin-top: 0.8em;
						margin-bottom: 0.5em;
						text-align: center;">
					Reset your password
				</h1>
				<p class=""
				   style="box-sizing: inherit;
						margin: 0;
						padding: 0;
						color: #3a3a3a;
						line-height: 1.6;
						margin-top: 0.8em;
						margin-bottom: 1.8em;
						text-align: center;">
					Click <a href="{{ url('/') . '/reset-password/' . $token }}">here</a> to reset your password.
				</p>
				<p style="box-sizing: inherit;margin: 0;padding: 0;text-align: center;">
					<a class="--small" href="{{ url('/') . '/reset-password/' . $token }}"
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
							   background: {{ $hub->branding_primary_button }};
							   border: 1px solid {{ $hub->branding_primary_button }};
							   color: {{ $hub->branding_primary_button_text }};
							   font-size: 1em;">
						Reset your password</a>
				</p>
			</td>
		</tr>
	</table>
@endsection