@if(!empty($actions))
<div class="actions">
	@foreach($actions as $i => $item)
		<a class="btn btn-primary btn-sm actions__item" href="{{ $item['href'] }}">{!! ab(attr($item, 'icon'), '<i class="fa ' . attr($item, 'icon') . '"></i> ') !!}{{ $item['text'] }}</a>
	@endforeach
</div>
@endif