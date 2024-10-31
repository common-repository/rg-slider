<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Slideshow {

	public function create()
	{
		// Verify nonce
		if (empty($_POST) || ! wp_verify_nonce($_POST['create_slideshow_nonce'], 'create_slideshow'))
		{
			wp_die(__('An error has occurred while validating your nonce.', 'rg-slider'));
		}

		// Set slideshow name
		$slideshow_name = esc_attr($_POST['create_slideshow']);

		// If no slideshow name is filled in, we can't add one.
		if(empty($slideshow_name))
		{
			set_transient('rg-slider-notifications', __('Please supply a name for the slideshow.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider', FALSE) . '&slideshow=new');
			exit;
		}

		// If the slideshow already exists, we also can't add it.
		if(term_exists($slideshow_name, 'rg_slides_slideshow'))
		{
			set_transient('rg-slider-notifications', __('There is already a slideshow with this name.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider', FALSE) . '&slideshow=new');
			exit;
		}

		// Add the slideshow to the slideshow taxonomy
		$slideshow_id = wp_insert_term($slideshow_name, 'rg_slides_slideshow');

		// Get slideshow slug
		$slideshow_data = get_term_by('id', $slideshow_id['term_id'], 'rg_slides_slideshow');
		$show_slug = $slideshow_data->slug;

		// Get default options
		$default_options = get_option('rg_slider_default');

		add_option('rg_slider_' . $show_slug, $default_options);

		// Update registered options
		$registered_options = get_option('rg_slider_registered_options');
		array_push($registered_options, 'rg_slider_' . $show_slug);
		update_option('rg_slider_registered_options', $registered_options);

		set_transient('rg-slider-notifications', __('The slideshow has succesfully been added.', 'rg-slider'), 60);
		wp_redirect(menu_page_url('rg-slider', FALSE) . '&slideshow=' . $show_slug);
		exit;
	}

	//------------------------------------------------------------------------

	public function update()
	{
		if(empty($_POST['show_slug']))
		{
			set_transient('rg-slider-notifications', __('No slideshow was selected.', 'rg-slider'), 60);
			wp_redirect(menu_page_url('rg-slider-options', FALSE));
			exit;
		}

		$image_width = esc_attr($_POST['image_width']);
		$image_height = esc_attr($_POST['image_height']);
		$image_quality = esc_attr($_POST['image_quality']);
		$slideshow_delay = esc_attr($_POST['slideshow_delay']);
		$navigation = esc_attr($_POST['navigation']);
		$navigation_next_prev = esc_attr($_POST['navigation_next_prev']);
		$navigation_prev = esc_attr($_POST['navigation_prev']);
		$navigation_next = esc_attr($_POST['navigation_next']);
		$caption_opacity = esc_attr($_POST['caption_opacity']);
		$caption_bgcolor = esc_attr($_POST['caption_bgcolor']);
		$caption_textcolor = esc_attr($_POST['caption_textcolor']);

		if( ! is_numeric($image_width))
		{
			$image_width = 100;
		}

		if( ! is_numeric($image_height))
		{
			$image_height = 100;
		}

		if( ! is_numeric($image_quality) || $image_quality < 1 || $image_quality > 100)
		{
			$image_quality = 100;
		}

		if( ! is_numeric($slideshow_delay))
		{
			$slideshow_delay = 3000;
		}

		if($navigation != 0 && $navigation != 1)
		{
			$navigation = 1;
		}

		if($navigation_next_prev != 0 && $navigation_next_prev != 1)
		{
			$navigation_next_prev = 1;
		}

		if( ! is_numeric($caption_opacity) || $caption_opacity < 1 || $caption_opacity > 100)
		{
			$caption_opacity = 100;
		}

		if( ! preg_match('/^#[a-f0-9]{6}$/i', $caption_bgcolor))
		{
			$caption_bgcolor = '#000000';
		}

		if( ! preg_match('/^#[a-f0-9]{6}$/i', $caption_textcolor))
		{
			$caption_textcolor = '#ffffff';
		}

		// Update the options options
		update_option('rg_slider_' . esc_attr($_POST['show_slug']), array(
			'slide_width' => $image_width,
			'slide_height' => $image_height,
			'quality' => $image_quality,
			'delay' => $slideshow_delay,
			'navigation' => $navigation,
			'next_prev' => $navigation_next_prev,
			'prev' => $navigation_prev,
			'next' => $navigation_next,
			'caption_opacity' => $caption_opacity,
			'caption_bgcolor' => $caption_bgcolor,
			'caption_textcolor' => $caption_textcolor
		));

		set_transient('rg-slider-notifications', __('The slideshow options have succesfully been updated.', 'rg-slider'), 60);
		wp_redirect(menu_page_url('rg-slider-options', FALSE) . '&slideshow=' . $_POST['show_slug']);
		exit;
	}

	//------------------------------------------------------------------------

	public function delete($id = NULL)
	{
		$show_id = esc_attr($_POST['slideshow_id']);

		if( ! empty($id))
		{
			$show_id = $id;
		}

		if(empty($show_id))
		{
			wp_redirect(menu_page_url('rg-slider', FALSE));
			exit;
		}

		// Set args to get slides
		$args = array(
			'post_type' => 'rg_slides',
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'id',
					'terms' => $show_id
				)
			)
		);

		// Get all slides related to the slideshow
		$posts = get_posts($args);

		// Delete all related slides and unlink images
		foreach($posts AS $p)
		{
			RG_Slider_Slide::delete($p->ID);
		}

		wp_delete_term($show_id, 'rg_slides_slideshow');

		if( ! empty($id))
		{
			return;
		}

		wp_redirect(menu_page_url('rg-slider', FALSE));
		exit;
	}

	//------------------------------------------------------------------------

	public static function display($show_slug)
	{
		// Create the query for the requested slideshow
		$slides = new WP_Query(array(
			'post_type' => 'rg_slides',
			'posts_per_page' => -1,
			'meta_key' => 'slide_order',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'slug',
					'terms' => $show_slug
				)
			)
		));

		// Get the options
		$options = get_option('rg_slider_' . $show_slug);

		// Get upload folder settings
		$upload = wp_upload_dir();

		// Enqueue the slidesjs script and rg_slider template script
		wp_enqueue_script('coin-slider');
		wp_enqueue_script('rg-slider');

		// Localize the rg_slider script and show it the show slug and options
		wp_localize_script('rg-slider', 'rg_slider', array(
			'show_slug' => $show_slug,
			'slideshow_options' => $options
		));

		$content = '<div><div id="rg_slider_' . $show_slug . '">';

		if($slides->have_posts()) : while($slides->have_posts()) : $slides->the_post();

			$slide_meta = get_post_meta(get_the_ID(), 'slide_meta');
			$slide_link = ( ! empty($slide_meta[0]['slide_link'])) ? 'href="' . $slide_meta[0]['slide_link'] . '" target="_blank"' : 'href="javascript:void(0);"';

			$content .= '<a ' . $slide_link . '>
							<img src="' . $upload['baseurl'] . '/rg-slider/' . $slide_meta[0]['slide_name'] . '" alt="' . get_the_title(). '" class="rg_slider_slide">';

			if(get_the_content() != '') :

			$content .= '<span>
							' . get_the_content() . '
						</span>';
			endif;

			$content .=	'</a>';

		endwhile; endif;

		$content .= '</div></div>';

		wp_reset_query();

		return $content;
	}

}