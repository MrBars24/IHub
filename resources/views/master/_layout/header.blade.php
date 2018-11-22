<header id="layout-header" class="layout-header">
	<div class="container-fluid">
		<div class="layout-header__logo">
			@include('master._layout.brand')
		</div>
		<nav class="layout-header__navigation navigation">
			@if(isset($links))
				<div class="dropdown">
					<a href="#" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						<i class="navigation__item__icon fa fa-cog"></i>
						<span class="navigation__item__label">My Account</span>
					</a>
					<ul class="dropdown-menu">
						@foreach($links as $i => $item)
							<li>
								<a class="ss__trigger" href="{{ attr($item, 'href') }}">
									{{ attr($item, 'text') }}
								</a>
							</li>
						@endforeach
					</ul>
				</div>
			@endif
		</nav>
	</div>
</header>