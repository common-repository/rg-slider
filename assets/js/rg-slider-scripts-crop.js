;(function($) {

	$(function() {

		var jcrop_api,
			crop_image = $('#new_slide_image'),
			show_select = $('#new_image_slideshow'),
			data = {
				action : 'get_rg_slider_options',
				show_slug : show_select.val(),
				slide_id : $('#slide_id').val()
			},
			comp_data = {
				action : 'get_pages_and_posts'
			};

		//------------------------------------------------------------------------

		$.getJSON(ajaxurl, data, function(options, textStatus) {
			slide_x = options.slide_x;
			slide_y = options.slide_y;
			slide_x2 = options.slide_x2;
			slide_y2 = options.slide_y2;
			slide_w = options.slide_w;
			slide_h = options.slide_h;

			crop_image.Jcrop({
				allowSelect : false,
				setSelect   : [ slide_x, slide_y, slide_x2, slide_y2 ],
				onChange    : show_coords,
				onSelect    : show_coords,
				aspectRatio : slide_w / slide_h
			}, function() {
				jcrop_api = this;
			});
		});

		//------------------------------------------------------------------------

		show_select.on('change', function() {
			change_crop_selection($(this).val());
		});

		//------------------------------------------------------------------------

		$('#new_image_link').autocomplete({
			source: function(request, response) {
				$.getJSON(ajaxurl, comp_data, function(data) {
					response($.map(data, function(item) {
						return {
							label: item.label,
							value: item.value
						}
					}));
				});
			},
			minLength: 2,
			select: function(event, ui) {
				//custom select function if needed
			}
		});

		//------------------------------------------------------------------------

		$('#toplevel_page_rg-slider')
			.addClass('current')
			.removeClass('wp-not-current-submenu')
			.find('a')
			.addClass('current')
			.removeClass('wp-not-current-submenu');

		//------------------------------------------------------------------------

		function change_crop_selection(show_slug) {
			data.show_slug = show_slug;
			delete data.slide_id;
			$.getJSON(ajaxurl, data, function(options, textStatus) {
				jcrop_api.animateTo([ 0, 0, options.slide_w, options.slide_h ]);
				jcrop_api.setOptions({ aspectRatio : options.slide_w / options.slide_h});

				show_options = {
					x : options.slide_x,
					y : options.slide_y,
					x2 : options.slide_x2,
					y2 : options.slide_y2,
					w : options.slide_w,
					h : options.slide_h,
				}
				show_coords(show_options);
			});
		}

		//------------------------------------------------------------------------
				
		function show_coords(c) {
			$('#new_image_x').val(c.x);
			$('#new_image_y').val(c.y);
			$('#new_image_x2').val(c.x2);
			$('#new_image_y2').val(c.y2);
			$('#new_image_w').val(c.w);
			$('#new_image_h').val(c.h);
		}
		
	});

})(jQuery);