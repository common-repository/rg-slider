<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Slide {

	public function upload()
	{
		// Verify nonce
		if(empty($_POST) || ! wp_verify_nonce($_POST['add_new_slide_nonce'], 'add_new_slide'))
		{
			wp_die(__('An error has occurred while validating your nonce.', 'rg-slider'));
		}

		// Check if there are slideshows
		$slideshows = get_terms('rg_slides_slideshow', 'hide_empty=0');

		if(empty($slideshows))
		{
			set_transient('rg-slider-notifications', __('You first have to create a slideshow, then you can begin adding slides.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider', FALSE));
			exit;
		}

		// Validate the mimetype of the image
		if( ! validate_mimetype($_FILES['add_new_slide']['type']))
		{
			set_transient('rg-slider-notifications', __('The file you tried to upload was no image.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider', FALSE));
			exit;
		}

		// Process the file and save to filesystem
		if( ! $filedata = RG_Slider_Slide::handle_upload())
		{
			set_transient('rg-slider-notifications', __('An error has occurred while uploading the file.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider', FALSE));
			exit;
		}

		set_transient('rg-slider-new-slide', $filedata['url']);
		set_transient('rg-slider-new-slide-slug', esc_attr($_POST['show_slug']));
		wp_redirect(menu_page_url('rg-slider-crop-page', FALSE));
		exit;
	}

	//------------------------------------------------------------------------

	public function create()
	{
		delete_transient('rg-slider-new-slide');
		delete_transient('rg-slider-new-slide-slug');

		$slide = array(
			'post_content' => esc_attr($_POST['new_image_caption']),
			'post_status' => 'publish',
			'post_title' => end(explode('/', esc_attr($_POST['new_image_url']))),
			'post_type' => 'rg_slides'
		);

		$slide_id = wp_insert_post($slide);

		wp_set_object_terms($slide_id, esc_attr($_POST['new_image_slideshow']), 'rg_slides_slideshow');

		$slide_meta = array(
			'slide_name' => end(explode('/', esc_attr($_POST['new_image_url']))),
			'slide_x' => esc_attr($_POST['new_image_x']),
			'slide_y' => esc_attr($_POST['new_image_y']),
			'slide_x2' => esc_attr($_POST['new_image_x2']),
			'slide_y2' => esc_attr($_POST['new_image_y2']),
			'slide_w' => esc_attr($_POST['new_image_w']),
			'slide_h' => esc_attr($_POST['new_image_h']),
			'slide_link' => esc_attr($_POST['new_image_link'])
		);

		$highest_order = new WP_Query(array(
			'post_type' => 'rg_slides',
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'slug',
					'terms' => esc_attr($_POST['new_image_slideshow'])
				)
			),
			'meta_key' => 'slide_order',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		));

		if(empty($highest_order->posts))
		{
			$new_order = 1;
		}
		else
		{
			$meta = get_post_meta($highest_order->posts[0]->ID, 'slide_order');
			$order = $meta[0];
			$new_order = $order + 1;
		}

		update_post_meta($slide_id, 'slide_meta', $slide_meta);
		update_post_meta($slide_id, 'slide_order', $new_order);

		RG_Slider_Image::process($slide_id);

		set_transient('rg-slider-notifications', __('The slide has been successfully added.', 'rg-slider'), 60);

		$slideshow = wp_get_post_terms($slide_id, 'rg_slides_slideshow');

		wp_redirect(menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug);
		exit;
	}

	//------------------------------------------------------------------------

	public function update()
	{
		$slide = array(
			'ID' => $_POST['slide_id'],
			'post_content' => $_POST['new_image_caption']
		);

		$slide_id = wp_update_post($slide);

		wp_set_object_terms($slide_id, esc_attr($_POST['new_image_slideshow']), 'rg_slides_slideshow');

		$slide_meta = array(
			'slide_name' => esc_attr($_POST['slide_name']),
			'slide_x' => esc_attr($_POST['new_image_x']),
			'slide_y' => esc_attr($_POST['new_image_y']),
			'slide_x2' => esc_attr($_POST['new_image_x2']),
			'slide_y2' => esc_attr($_POST['new_image_y2']),
			'slide_w' => esc_attr($_POST['new_image_w']),
			'slide_h' => esc_attr($_POST['new_image_h']),
			'slide_link' => esc_attr($_POST['new_image_link'])
		);

		update_post_meta($slide_id, 'slide_meta', $slide_meta);

		RG_Slider_Image::process($slide_id);

		set_transient('rg-slider-notifications', __('The slide has been successfully updated.', 'rg-slider'), 60);

		$slideshow = wp_get_post_terms($slide_id, 'rg_slides_slideshow');

		wp_redirect(menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug);
		exit;
	}

	//------------------------------------------------------------------------

	public function delete($slide_id)
	{
		if(is_array($slide_id))
		{
			foreach($slide_id AS $id)
			{
				self::delete($id);
			}

			return TRUE;
		}

		// Get the slide data
		$slide = get_post($slide_id);

		// Get upload folder settings
		$upload = wp_upload_dir();

		// Remove slide
		wp_delete_post($slide->ID);

		// Delete images
		@unlink($upload['basedir'] . '/rg-slider/' . $slide->post_title);
		@unlink($upload['basedir'] . '/rg-slider/' . set_orig($slide->post_title));

		return;
	}

	//------------------------------------------------------------------------

	public static function disable($slide_id)
	{
		if(is_array($slide_id))
		{
			foreach($slide_id AS $id)
			{
				self::disable($id);
			}

			return TRUE;
		}

		$post = array(
			'ID' => $slide_id,
			'post_status' => 'draft'
		);

		$update = wp_update_post($post);

		if( ! $update)
		{
			return FALSE;
		}

		return TRUE;
	}

	//------------------------------------------------------------------------

	public static function enable($slide_id)
	{
		if(is_array($slide_id))
		{
			foreach($slide_id AS $id)
			{
				self::enable($id);
			}

			return TRUE;
		}

		$post = array(
			'ID' => $slide_id,
			'post_status' => 'publish'
		);

		$update = wp_update_post($post);

		if( ! $update)
		{
			return FALSE;
		}

		return TRUE;
	}

	//------------------------------------------------------------------------

	public static function moveup($slide_id)
	{
		// Get the slide_order of the given slide
		$slide_order = get_post_meta($slide_id, 'slide_order');
		$slide_order = $slide_order[0];

		// Set the max slide_order for the query
		$prev_order = $slide_order - 1;

		// Get the slideshow the slide belongs to
		$slideshow = wp_get_post_terms($slide_id, 'rg_slides_slideshow');

		// Get the previous slide in line
		$prev_slide = new WP_Query(array(
			'post_type' => 'rg_slides',
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'slug',
					'terms' => $slideshow[0]->slug
				)
			),
			'meta_key' => 'slide_order',
			'meta_query' => array(
				array(
					'key' => 'slide_order',
					'value' => $prev_order,
					'type' => 'numeric',
					'compare' => '<='
				)
			),
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		));

		if(empty($prev_slide->posts)) return FALSE;

		// Get the previous slides ID
		$prev_slide_id = $prev_slide->posts[0]->ID;

		// Get the previous slide slide_order
		$meta = get_post_meta($prev_slide_id, 'slide_order');
		$prev_order = $meta[0];

		// Switch the orders around
		update_post_meta($prev_slide_id, 'slide_order', $slide_order);
		update_post_meta($slide_id, 'slide_order', $prev_order);

		return TRUE;
	}

	//------------------------------------------------------------------------

	public static function movedown($slide_id)
	{
		// Get the slide_order of the given slide
		$slide_order = get_post_meta($slide_id, 'slide_order');
		$slide_order = $slide_order[0];

		// Set the max slide_order for the query
		$next_order = $slide_order + 1;

		// Get the slideshow the slide belongs to
		$slideshow = wp_get_post_terms($slide_id, 'rg_slides_slideshow');

		// Get the next slide in line
		$next_slide = new WP_Query(array(
			'post_type' => 'rg_slides',
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'slug',
					'terms' => $slideshow[0]->slug
				)
			),
			'meta_key' => 'slide_order',
			'meta_query' => array(
				array(
					'key' => 'slide_order',
					'value' => $next_order,
					'type' => 'numeric',
					'compare' => '>='
				)
			),
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
		));

		if(empty($next_slide->posts)) return FALSE;

		// Get the next slides ID
		$next_slide_id = $next_slide->posts[0]->ID;

		// Get the next slide slide_order
		$meta = get_post_meta($prev_slide_id, 'slide_order');
		$prev_order = $meta[0];

		// Switch the orders around
		update_post_meta($next_slide_id, 'slide_order', $slide_order);
		update_post_meta($slide_id, 'slide_order', $next_order);

		return TRUE;
	}

	//------------------------------------------------------------------------

	public static function handle_upload()
	{
		// Add filter for custom upload dir
		add_filter('upload_dir', array('RG_Slider_Slide', 'set_upload_dir'));

		// Set override
		$overrides = array('test_form' => FALSE);

		// Do the actual upload
		$filedata = wp_handle_upload($_FILES['add_new_slide'], $overrides);

		// Copy the image so we always have the original
		copy($filedata['file'], set_orig($filedata['file']));

		// Remove the filter again so future uploads will go into the original upload dir
		remove_filter('upload_dir', array('RG_Slider_Slide', 'set_upload_dir'));

		return $filedata;
	}

	//------------------------------------------------------------------------

	public static function set_upload_dir($upload)
	{
		// Change uploads vars for custom upload.
		// This way we can use Wordpress wp_handle_upload() function
		// for our image upload and use a custom upload dir.
		$upload['subdir'] = '/rg-slider';
		$upload['path'] = $upload['basedir'] . $upload['subdir'];
		$upload['url'] = $upload['baseurl'] . $upload['subdir'];

		return $upload;
	}

	//------------------------------------------------------------------------

	public static function get_options()
	{
		if(isset($_GET['slide_id']))
		{
			$slide_meta = get_post_meta(esc_attr($_GET['slide_id']), 'slide_meta');
			$options['slide_x'] = $slide_meta[0]['slide_x'];
			$options['slide_y'] = $slide_meta[0]['slide_y'];
			$options['slide_x2'] = $slide_meta[0]['slide_x2'];
			$options['slide_y2'] = $slide_meta[0]['slide_y2'];
			$options['slide_w'] = $slide_meta[0]['slide_w'];
			$options['slide_h'] = $slide_meta[0]['slide_h'];
		}
		else
		{
			$slideshow_options = get_option('rg_slider_' . esc_attr($_GET['show_slug']));
			$options['slide_x'] = 0;
			$options['slide_y'] = 0;
			$options['slide_x2'] = $slideshow_options['slide_width'];
			$options['slide_y2'] = $slideshow_options['slide_height'];
			$options['slide_w'] = $slideshow_options['slide_width'];
			$options['slide_h'] = $slideshow_options['slide_height'];
		}

		echo json_encode($options);
		exit();
	}

}