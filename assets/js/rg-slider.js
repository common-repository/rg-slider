;(function($) {
	$(function() {

		if(rg_slider.slideshow_options.navigation == '1') {
			nav = true;
		} else {
			nav = false;
		}

		$('#rg_slider_' + rg_slider.show_slug).coinslider({
			width: rg_slider.slideshow_options.slide_width,
			height: rg_slider.slideshow_options.slide_height,
			spw: 7,
			sph: 5,
			delay: parseInt(rg_slider.slideshow_options.delay, 10),
			sDelay: 60,
			opacity: parseInt(rg_slider.slideshow_options.caption_opacity, 10) / 100,
			titleSpeed: 500,
			effect: 'random',
			navigation: nav,
			links : true,
			hoverPause: true,
			prev: rg_slider.slideshow_options.prev,
			next: rg_slider.slideshow_options.next
		});

		$('#cs-title-rg_slider_' + rg_slider.show_slug).css({
			'backgroundColor' : rg_slider.slideshow_options.caption_bgcolor,
			'color' : rg_slider.slideshow_options.caption_textcolor
		});

		if(rg_slider.slideshow_options.next_prev == 0) {
			$('#cs-buttons-rg_slider_' + rg_slider.show_slug).hide();
		}

	});
})(jQuery);