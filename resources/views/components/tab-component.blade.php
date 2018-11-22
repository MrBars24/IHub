@php
	$tab_content_prefix = 'tab-content';
@endphp

@if(isset($tabs) && isset($part) && $part == 'tab-pane-top')
	@php
		$tab_pane_id = 'tab-pane' . (isset($tabs['id']) ? '-' . $tabs['id'] : '');
	@endphp
	<div class="tab-pane" id="{{ $tab_pane_id }}" role="tabpanel" aria-labelledby="{{ $tab_pane_id }}">
		<ul class="tab-pane__nav" role="tablist">
			@foreach($tabs['items'] as $key => $item)
				<li class="tab-pane__nav__link{{ isset($item['active']) && $item['active'] ? ' --active' : '' }}">
					<a id="tab-{{ $key }}" data-toggle="tab" href="#{{ $tab_content_prefix }}-{{ $key }}" role="tab" aria-controls="{{ $tab_content_prefix }}-{{ $key }}"{{ isset($item['active']) && $item['active'] ? ' aria-selected="true"' : '' }}>{{ $item['label'] }}</a>
				</li>
			@endforeach
		</ul>
		<div class="tab-pane__container">
@endif

@if(isset($tabs) && isset($part) && $part == 'tab-pane-bottom')
		</div>
	</div>
@endif

@if(isset($tabs) && isset($part) && $part == 'tab-content-top')
	@php
		$tab_content_id = $tab_content_prefix . (isset($tab) ? '-' . $tab : '');
	@endphp
	<div class="tab-pane__content{{ isset($tabs['items'][$tab]['active']) && $tabs['items'][$tab]['active'] ? ' --active' : '' }}" id="{{ $tab_content_id }}" role="tabpanel" aria-labelledby="{{ $tab_content_id }}">
@endif

@if(isset($tabs) && isset($part) && $part == 'tab-content-bottom')
	</div>
@endif