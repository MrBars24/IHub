@extends('email.layout.standard', ['hub' => $hub])

@section('content-inner')
	<table class="email-notification" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;position: relative;margin-bottom: 10px;width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td>
				<h1 class="email-intro__title" style="box-sizing: inherit;margin: 0.8em 0;padding: 0 20px;font-size: 2em;color: #3a3a3a; text-align: center;">Hello {{ $user->name }},</h1>
				<p class="email-intro__message" style="box-sizing: inherit;margin: 0;padding: 0 20px;color: #3a3a3a;font-size: 1.2em;line-height: 1.7;margin-bottom: 1.6em;text-align: center;">Check out our latest gigs&hellip;</p>
			</td>
		</tr>
	</table>

	@foreach($gigs as $i => $gig)
		<table class="email-gig" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;position: relative;margin-top: 2em;margin-bottom: 4em;width: 100%;">
			<tr style="box-sizing: inherit;margin: 0;padding: 0;">
				<td width="100%" class="email-gig__details" style="box-sizing: inherit;margin: 0;padding: 0 60px;text-align: left;">
					<div style="box-sizing: inherit;margin: 0;padding: 0;">
						<div style="float:right;width:30%;text-align:right;">
							<a class="email-gig__view button --small" href="{{ $alert->getGigClickthroughUrl($hub, $gig) }}" 
								style="box-sizing: inherit;
									margin: 0;
									padding: 7px 20px;
									text-decoration: none;
									-webkit-tap-highlight-color: transparent;
									background-color: 
									transparent;
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
									font-size: 1em;
									float: right;">
								View
							</a>
						</div>
						<h3 class="email-gig__title" style="box-sizing: inherit;margin: 0;padding: 0;color: #000000;float: left;font-size: 1.4em;margin-bottom: 2px;width: 70%;"><a href="{{ $alert->getGigClickthroughUrl($hub, $gig) }}" style="color: #000000;">{{ $gig->title }}</a></h3>
						<span class="email-gig__deadline" style="font-size: 0.85em;box-sizing: inherit;margin: 0;padding: 0;color: #969696;display: block;float: left;margin-bottom: 0.5em;width: 70%;"><i class="fa fa-clock-o" style="box-sizing: inherit;margin: 0;padding: 0;"></i> Listing ends in <time style="box-sizing: inherit;margin: 0;padding: 0;">{{ relativeDate($gig->deadline_at) }}</time></span>
						<p class="email-gig__description" style="box-sizing: inherit;margin: 0;padding: 0;clear: both;color: #3a3a3a;line-height: 1.6;margin-top: 18px;margin-bottom: 0.8em;">{{ $gig->description }}</p>
					</div>
					<span class="email-gig__reward" style="box-sizing: inherit;margin: 0;padding: 0;color: #dc4020;"><i class="fa fa-star" style="box-sizing: inherit;margin: 0;padding: 0;"></i> Reward: {{ $gig->points }} points
						@if($gig->relationLoaded('rewards') && !$gig->rewards->isEmpty())
							<span class="email-gig__reward__other" style="box-sizing: inherit;margin: 0;padding: 0;color: #8c2914;font-size: 80%;">(+ {{ $gig->rewards->count() }} more)</span>
						@endif
					</span>
				</td>
			</tr>
		</table>
	@endforeach

	<table class="buttons" style="box-sizing: inherit;margin: 0;padding: 0;border-collapse: collapse;border-spacing: 0;position: relative;margin-top: 2em;margin-bottom: 4em;width: 100%;">
		<tr style="box-sizing: inherit;margin: 0;padding: 0;">
			<td class="button-holder" width="50%" style="box-sizing: inherit;margin: 0;padding: 0px 5px 0px 10px;border-radius: 50%;vertical-align: top;text-align: right;">
				<a class="button --primary" href="{{ route('hub::gig.list', ['hub' => $hub->slug]) }}" 
					style="
						box-sizing: inherit;
						margin: 0;
						padding: 10px 17px;
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
						background: transparent;
						font-size: 1.15em;
						color: {{ $hub->branding_primary_button_text }};			
						background: {{ $hub->branding_primary_button }};
						border: 1px solid {{ $hub->branding_primary_button }};
						color: #ffffff;">
					Find more gigs
				</a>
			</td>
			<td class="button-holder" width="50%" style="box-sizing: inherit;margin: 0;padding: 0 10px 0 5px;border-radius: 50%;vertical-align: top;text-align: left;">
				<a class="button --tertiary" href="{{ route('hub::settings', ['hub' => $hub->slug, 'tab' => 'alerts']) }}" 
					style="
						box-sizing: inherit;
						margin: 0;
						padding: 10px 17px;
						color: {{ $hub->branding_primary_button }};
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
						background: transparent;
						border: 1px solid {{ $hub->branding_primary_button }};
						font-size: 1.15em;">
						Email settings</a>
			</td>
		</tr>
	</table>
	<img src="{{ $alert->getAlertPingUrl($hub) }}" alt="" />
@endsection