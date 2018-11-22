<aside class="sidebar">
	<ul>
		@foreach($sidebar as $key => $item)
			<li class="sidebar__item{{ ab($key == $active_sidebar, ' --active') }}">
				<a href="{{ $item['href'] }}">
					<i class="fa {{ $item['icon'] }}"></i>
					<span class="sidebar__item__label">{{ $item['label'] }}</span>
				</a>
			</li>
		@endforeach
	</ul>
</aside>