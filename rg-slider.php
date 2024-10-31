<?php

/*
Plugin Name: RG Slider
Plugin URI: http://robgloudemans.nl/rg-slider
Description: A jQuery slideshow on your website/blog made easy
Version: 1.4.3
Author: Rob Gloudemans
Author URI: http://robgloudemans.nl
License: GPLv2
Text Domain: rg-slider
Domain Path: /lang/
*/

$plugin_header_translation = array(
	__('A jQuery slideshow on your website/blog made easy', 'rg-slider')
);

define('RG_SLIDER', TRUE);

require_once('rg-slider-helpers.php');
require_once('rg-slider-posttype.php');
require_once('rg-slider-mainpage.php');
require_once('rg-slider-croppage.php');
require_once('rg-slider-optionspage.php');
require_once('rg-slider-slideshow.php');
require_once('rg-slider-slide.php');
require_once('rg-slider-listtable.php');
require_once('rg-slider-image.php');

class RG_Slider {

	public function __construct()
	{
		// Load plugin textdomain
		add_action('plugins_loaded', array($this, 'plugin_textdomain'));

		// Register function for plugin activation
		register_activation_hook(__FILE__, array($this, 'activate'));

		// Register function for plugin deactivation
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		// Add custom post type and taxonomy for RG Slider
		new RG_Slider_Posttype();

		// Setup admin page
		new RG_Slider_Mainpage();

		// Setup crop page
		new RG_Slider_Croppage();

		// Add action for slide upload action
		add_action('admin_action_upload_slide', array('RG_Slider_Slide', 'upload'));
		add_action('admin_action_create_slide', array('RG_Slider_Slide', 'create'));
		add_action('admin_action_update_slide', array('RG_Slider_Slide', 'update'));
		add_action('admin_action_create_slideshow', array('RG_Slider_Slideshow', 'create'));
		add_action('admin_action_update_slideshow', array('RG_Slider_Slideshow', 'update'));
		add_action('admin_action_delete_slideshow', array('RG_Slider_Slideshow', 'delete'));

		// If a notification transient is set, show it on the page via the admin_notices action
		if(get_transient('rg-slider-notifications'))
		{
			add_action('admin_notices', array($this, 'rg_slider_admin_notifications'));
		}

		// Setup options page
		new RG_Slider_Optionspage();

		// Add action to enqueue scripts and styles for the backend
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		// Add action to enqueue scripts for the frontend
		add_action('wp_enqueue_scripts', array($this, 'register_frontend_scripts'));

		// Add action for JavaScript AJAX request for options
		add_action('wp_ajax_get_rg_slider_options', array('RG_Slider_Slide', 'get_options'));

		// Add action for JavaScript AJAX request for pages and posts
		add_action('wp_ajax_get_pages_and_posts', array('RG_Slider', 'get_pages_and_posts'));
	}

	//------------------------------------------------------------------------

	public function plugin_textdomain()
	{
		load_plugin_textdomain('rg-slider', FALSE, dirname(plugin_basename(__FILE__)) . '/lang/');
	}

	//------------------------------------------------------------------------

	public function activate()
	{
		// Get upload folder settings
		$upload = wp_upload_dir();

		// Create slider folder in uploads folder
		mkdir($upload['basedir'] . '/rg-slider');

		// Add default options
		add_option('rg_slider_default', array(
			'slide_width' => 400,
			'slide_height' => 400,
			'quality' => 80,
			'delay' => 5000,
			'navigation' => 1,
			'next_prev' => 1,
			'prev' => __('Previous'),
			'next' => __('Next'),
			'caption_opacity' => 70,
			'caption_bgcolor' => '#000000',
			'caption_textcolor' => '#ffffff',
		));

		// Add an options to keep track of the registered options
		// (so we can delete everything on deactivation)
		add_option('rg_slider_registered_options', array(
			'rg_slider_default'
		));
	}

	//------------------------------------------------------------------------

	public function deactivate()
	{
		// Get upload folder settings
		$upload = wp_upload_dir();

		// Remove slides folder from uploads folder
		rrmdir($upload['basedir'] . '/rg-slider');

		// We also want to delete all slideshows and slides
		$slideshows = get_terms('rg_slides_slideshow', 'hide_empty=0');

		foreach($slideshows AS $show)
		{
			RG_Slider_Slideshow::delete($show->term_id);
		}

		// Get all registered options
		$options = get_option('rg_slider_registered_options');

		// And delete all registered options
		foreach($options AS $option)
		{
			delete_option($option);
		}

		// And finally, delete the registered options option
		delete_option('rg_slider_registered_options');
	}

	//------------------------------------------------------------------------

	public function enqueue_admin_scripts($hook)
	{
		// Always load the css for the menu-icon
		wp_enqueue_style('rg-slider-menu', plugins_url('assets/css/rg-slider-menu-style.css', __FILE__));

		// Also enqueue the main stylesheet
		wp_enqueue_style('rg-slider-main', plugins_url('assets/css/rg-slider-style.css', __FILE__));

		if($hook == 'admin_page_rg-slider-crop-page')
		{
			// Enqueue the jcrop script and style
			wp_enqueue_script('jcrop');
			wp_enqueue_style('jcrop');

			// Because of our hack with the submenu page without a parent,
			// we need this extra javascript script to set the active menu item
			// Only cosmetic
			wp_enqueue_script('rg-slider-crop', plugins_url('assets/js/rg-slider-scripts-crop.js', __FILE__), array('jquery', 'jcrop', 'jquery-ui-autocomplete'), '1.0', TRUE);
		}

		if($hook == 'rg-slider_page_rg-slider-options')
		{
			wp_enqueue_style('wp-color-picker');

			wp_enqueue_script('rg-slider-options', plugins_url('assets/js/rg-slider-scripts-options.js', __FILE__), array('jquery', 'wp-color-picker'), '1.0', TRUE);
		}

		// And enqueue the main scripts file
		wp_enqueue_script('rg-slide-main', plugins_url('assets/js/rg-slider-scripts.js', __FILE__), array('jquery'), '1.0', TRUE);

		// Localize the main scripts file and add the l10n object for translation
		wp_localize_script('rg-slide-main', 'rg_l10n_object', array(
			'browse' => __('Browse...', 'rg-slider'),
		));
	}

	//------------------------------------------------------------------------

	public function register_frontend_scripts()
	{
		wp_enqueue_style('coin-slider', plugins_url('assets/css/coin-slider-styles.css', __FILE__), array(), '1.0');
		wp_register_script('coin-slider', plugins_url('assets/js/coin-slider.js', __FILE__), array('jquery'), '1.2.0', TRUE);

		wp_register_script('rg-slider', plugins_url('assets/js/rg-slider.js', __FILE__), array('jquery'), '1.0', TRUE);
	}

	//------------------------------------------------------------------------

	public function rg_slider_admin_notifications()
	{
		echo '<div class="updated"><p>' . get_transient('rg-slider-notifications') . '</p></div>';
		delete_transient('rg-slider-notifications');
	}

	//------------------------------------------------------------------------

	public function get_pages_and_posts()
	{
		require(ABSPATH . WPINC . '/class-wp-editor.php');
		$results = _WP_Editors::wp_link_query();

		$result = array();

		foreach($results AS $r)
		{
			$temp = array(
				'label' => $r['info'] . ' - ' . $r['title'],
				'value' => $r['permalink']
			);

			array_push($result, $temp);
		}

		echo json_encode($result);
		wp_die();
	}

}

// Get the ball rollin...
new RG_Slider();

// Create the rg_slider function
if( ! function_exists('rg_slider'))
{
	function rg_slider($show_slug)
	{
		return RG_Slider_Slideshow::display($show_slug);
	}
}

// Alias the main rg_nivoslider function to a shortcode
add_shortcode('rg-slider', 'rg_slider_shortcode');

function rg_slider_shortcode($atts)
{
	if( ! array_key_exists('show', $atts))
	{
		return;
	}

	return rg_slider($atts['show']);
}