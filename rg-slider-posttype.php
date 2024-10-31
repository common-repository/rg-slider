<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Posttype {

	public function __construct()
	{
		add_action('init', array($this, 'add_custom_posttype'));
		add_action('init', array($this, 'add_custom_taxonomy'));
	}

	//------------------------------------------------------------------------

	public function add_custom_posttype()
	{
		// Set the slides posttype settings
		$posttype = array(
			'labels' => array(
							'name' => 'RG Slides',
							'singular_name' => 'RG Slide',
							'add_new' => __('New slide', 'rg-slider'),
							'add_new_item' => __('Add new slide', 'rg-slider'),
							'edit_item' => __('Edit slide', 'rg-slider'),
							'new_item' => __('New slide', 'rg-slider'),
							'all_items' => __('All slides', 'rg-slider'),
							'view_item' => __('View slide', 'rg-slider'),
							'search_items' => __('Search slide', 'rg-slider'),
							'not_found' => __( 'No slides found', 'rg-slider'),
							'not_found_in_trash' => __('No slides found in trash', 'rg-slider'),
							'parent_item_colon' => __('Parent slide:', 'rg-slider'),
							'menu_name' => __('Slides', 'rg-slider')

						),
			'description' => __('Custom post type used by RG slider plugin', 'rg-slider'),
			'public' => FALSE, // We don't want it publicly accessible (available in menu)
			'hierarchical' => FALSE,
			'query_var' => 'rg_slides',
			'has_archive' => FALSE,
			'rewrite' => array(
				'slug' => 'rg_slides'
			),
			'supports' => array( 
				'title',  
				'editor'
			)
		); 
		
		// Register the slides posttype
		register_post_type('rg_slides', $posttype);
	}

	//------------------------------------------------------------------------

	public function add_custom_taxonomy()
	{
		// Set slideshow taxonomy settings
		$taxonomy = array(
			'labels' => array(
				'name' => 'RG Slideshows',
				'singular_name' => 'RG Slideshow',
				'search_items' => __('Search slideshow', 'rg-slider'),
				'all_items' => __('All slideshows', 'rg-slider'),
				'parent_item' => __('Parent slideshow', 'rg-slider'),
				'parent_item_colon' => __('Parent slideshow:', 'rg-slider'),
				'edit_item' => __('Edit slideshow', 'rg-slider'),
				'update_item' => __('Update slideshow', 'rg-slider'),
				'add_new_item' => __('Add new slideshow', 'rg-slider'),
				'new_item_name' => __('New slideshow name', 'rg-slider'),
				'popular_items' => __('Popular slideshows', 'rg-slider'),
				'menu_name' => __('Slideshow', 'rg-slider')
			),
			'hierarchical' => TRUE,
			'show_ui' => FALSE,
			'query_var' => 'rg_slides_slideshow',
			'has_archive' => FALSE,
			'rewrite' => array(
				'slug' => 'rg_slides/slideshow'
			)
		);

		// Register the slideshow taxonomy
		register_taxonomy('rg_slides_slideshow', 'rg_slides', $taxonomy);
	}

}