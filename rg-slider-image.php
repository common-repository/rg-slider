<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Image {

	public static function process($slide_id)
	{
		if(empty($slide_id))
		{
			return FALSE;
		}

		// Get slide settings
		$slide_data = get_post($slide_id);
		$slide_meta = get_post_meta($slide_id, 'slide_meta');
		$slideshow = wp_get_post_terms($slide_id, 'rg_slides_slideshow');
		$show_options = get_option('rg_slider_' . $slideshow[0]->slug);

		// Get upload folder settings
		$upload = wp_upload_dir();

		// Get the original and new slide paths
		$slide_path = $upload['basedir'] . '/rg-slider/' . set_orig($slide_data->post_title);
		$new_slide_path = $upload['basedir'] . '/rg-slider/' . $slide_data->post_title;

		// Create a new editor instance
		$editor = wp_get_image_editor($slide_path);

		// Check if we got a real editor instance
		if(is_wp_error($editor)) 
		{
			return FALSE;
		}

		// Set the image quality
		$editor->set_quality($show_options['quality']);

		// Get the original image dimensions
		$original_dimensions = $editor->get_size();

		// Get the dimension difference
		$difference = $original_dimensions['width'] / 600;

		// Calculate the dimensions as it was shown on the screen
		$display_dimensions = array(
			'width' => 600,
			'height' => $original_dimensions['height'] / $difference
		);

		$crop_dimensions = array(
			'x' => $slide_meta[0]['slide_x'] * $difference,
			'y' => $slide_meta[0]['slide_y'] * $difference,
			'width' => $slide_meta[0]['slide_w'] * $difference,
			'height' => $slide_meta[0]['slide_h'] * $difference
		);

		// Let's crop the image
		$editor->crop($crop_dimensions['x'], $crop_dimensions['y'], $crop_dimensions['width'], $crop_dimensions['height'], $show_options['slide_width'], $show_options['slide_height']);

		// Save the new cropped image
		$editor->save($new_slide_path);

		return TRUE;
	}

}