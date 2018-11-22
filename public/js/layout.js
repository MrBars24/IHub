/**
 * aside.js
 */
jQuery(function($) {
	var height = $('.main__aside.--pulldown > *').outerHeight();
	$('.main__aside.--pulldown').css('margin-top', -(height - 10));

	$('.main__aside.--pulldown .main__aside__trigger').click(function(e) {
		e.preventDefault();
		$('.main__aside.--pulldown').toggleClass('--active');
		if($('.main__aside.--pulldown').hasClass('--active')) {
			$('.main__aside.--pulldown').css('margin-top', 0);
		} else {
			var height = $('.main__aside.--pulldown > *').outerHeight();
			$('.main__aside.--pulldown').css('margin-top', -(height - 10));
		}
	});
});
//# sourceMappingURL=layout.js.map
