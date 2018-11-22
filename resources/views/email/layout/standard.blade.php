@extends('email.layout.master')

@section('header')
	<!-- Header -->
	<table class="email-header" 
		style="box-sizing: inherit;
			margin: 0;
			padding: 0;
			border-collapse: collapse;
			border-spacing: 0;
			text-align: center;
			width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
			<td class="email-container" 
				style="box-sizing: inherit;
					margin: 0 auto;
					padding: 0;
					display: block;
					max-width: 600px;">

				<div class="email-container__content" 
					style="box-sizing: inherit;
						margin: 0 auto;
						padding: 1.5em 0;
						display: block;max-width: 600px;">
					<table bgcolor="{{ $hub->email_header_colour }}" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;width: 100%;border: 1px solid #e9eced;border-bottom: 3px solid #dbe0e2;">
						<tr style="box-sizing: inherit;margin: 0;padding: 0;">
							<td align="center" style="box-sizing: inherit;margin: 0;padding: 25px 0;">
								<a class="logo" href="{{ url('/') }}"
									style="box-sizing: inherit;
										margin: 0;
										padding: 0;
										color: inherit;
										text-decoration: none;
										-webkit-tap-highlight-color: transparent;
										background-color: transparent;
										background-image: none !important;
										background-position: left center;
										background-size: contain;
										background-repeat: no-repeat;
										display: inline-block;
										text-indent: 0 !important;
										width: 155px;
										height: auto !important;
										cursor: pointer;">
									<img src="{{ $hub->email_logo_web_path }}"
										style="box-sizing: inherit;
											margin: 0;
											padding: 0;
											max-width: 100%;
											max-height: 100%;
											border: 0;">
								</a>
							</td>
						</tr>
					</table>
				</div>

			</td>
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
		</tr>
	</table>
@endsection

@section('content')
	<!-- Content -->
	<table class="email-main" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
			<td class="email-container" style="box-sizing: inherit;margin: 0 auto;padding: 0;display: block;max-width: 600px;">

				<div class="email-container__content" style="box-sizing: inherit;margin: 0 auto;padding: 1.5em 0;display: block;max-width: 600px;background-color: #ffffff;border: 1px solid #e9eced;border-bottom: 3px solid #dbe0e2;">
					@yield('content-inner')
				</div>

			</td>
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
		</tr>
	</table>
@endsection

@section('footer')
	<!-- Footer -->
	<table class="email-footer" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;text-align: center;width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
			<td class="email-container" style="box-sizing: inherit;margin: 0 auto;padding: 0;display: block;max-width: 600px;">

				<div class="email-container__content" style="box-sizing: inherit;margin: 0 auto;padding: 1.5em 0;display: block;max-width: 600px;">
					<table bgcolor="{{ $hub->email_footer_colour }}" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;width: 100%;border: 1px solid #e9eced;border-bottom: 3px solid #dbe0e2;">
						<tr style="box-sizing: inherit;margin: 0;padding: 0;">
							<td align="center" style="box-sizing: inherit;margin: 0;padding: 25px 0;">
								<a class="logo" href="{{ url('/') }}" style="box-sizing: inherit;
									margin: 0;
									padding: 0;
									color: inherit;
									text-decoration: none;
									-webkit-tap-highlight-color: transparent;
									background-color: transparent;
									background-image: none !important;
									background-position: left center;
									background-size: contain;
									background-repeat: no-repeat;
									display: inline-block;
									text-indent: 0 !important;
									width: 155px;
									height: auto !important;
									cursor: pointer;">
									<img src="{{ $hub->email_logo_web_path }}" alt="" style="box-sizing: inherit;margin: 0;padding: 0;max-width: 100%;max-height: 100%;border: 0;">
								</a>
								<ul class="email-footer__menu" style="
										box-sizing: inherit;
										margin: 1em 0 0 0 !important;
										padding: 0;
										list-style-type: none;
										color: {{ $hub->email_footer_text_1 }};
										font-size: 13px;
										text-align: center;">
									<li class="email-footer__menu__item" style="box-sizing: inherit;margin: 0 1em;padding: 0;list-style-type: none;color: inherit;display: inline-block;"><a href="http://influencerhub.com/" style="color: #ffffff !important; box-sizing: inherit;margin: 0;padding: 0;color: inherit;text-decoration: none;-webkit-tap-highlight-color: transparent;background-color: transparent; font-size: 13px;">Home</a></li>
									<li class="email-footer__menu__item" style="box-sizing: inherit;margin: 0 1em;padding: 0;list-style-type: none;color: inherit;display: inline-block;"><a href="http://influencerhub.com/privacy/" style="color: #ffffff !important; box-sizing: inherit;margin: 0;padding: 0;color: inherit;text-decoration: none;-webkit-tap-highlight-color: transparent;background-color: transparent; font-size: 13px;">Privacy</a></li>
									<li class="email-footer__menu__item" style="box-sizing: inherit;margin: 0 1em;padding: 0;list-style-type: none;color: inherit;display: inline-block;"><a href="{{ url('/') }}/{{ $hub->slug }}/settings" style="color: #ffffff !important; box-sizing: inherit;margin: 0;padding: 0;color: inherit;text-decoration: none;-webkit-tap-highlight-color: transparent;background-color: transparent; font-size: 13px;">Account Settings</a></li>
								</ul>
								<span class="email-footer__copyright" 
									style="
										box-sizing: inherit;
										margin: 20px 0 12px 0;
										padding: 0;
										color: {{ $hub->email_footer_text_2 }};
										display: block;
										font-size: 11px;
										text-align: center;">
									Copyright &copy; Influencer HUB {{ date('Y') }}
								</span>
							</td>
						</tr>
					</table>
				</div>

			</td>
			<td style="box-sizing: inherit;margin: 0;padding: 0;"></td>
		</tr>
	</table>
@endsection