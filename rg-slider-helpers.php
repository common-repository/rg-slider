<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

/**
 * Recursive remove dir
 *
 * Remove a folder including all contents and subfolders
 *
 * @param string $dir Directory to remove
 */
if( ! function_exists('rrmdir'))
{
	function rrmdir($dir)
	{
		$files = glob($dir . '/{,.}*', GLOB_BRACE);

		foreach($files AS $file)
		{
			@unlink($file);
		}

		rmdir($dir);
	}
}

//------------------------------------------------------------------------

/**
 * Validate mimetype
 *
 * Takes the file mimetype and checks if it's one of the mimetypes that
 * will be accepted
 *
 * @param string $mimetype Mimetype of file
 */
if( ! function_exists('validate_mimetype'))
{
	function validate_mimetype($mimetype)
	{
		return in_array($mimetype, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png'));
	}
}

//------------------------------------------------------------------------

/**
 * Change a filename to include _orig, so it points to the original image
 *
 * @param string $file Filename
 */
if( ! function_exists('set_orig'))
{
	function set_orig($file)
	{
		$ext = substr(strrchr($file, '.'), 0);
  		$file = str_replace($ext, '', $file);
  		$filename = $file . '_orig' . $ext;
  		return $filename;
	}
}

//------------------------------------------------------------------------

/**
 * Get the show slug bassed on $_GET or existing terms
 */
if( ! function_exists('get_show_slug'))
{
	function get_show_slug($default = 'new')
	{
		// If a slideshow slug is set in $_GET we want to show that slideshow
		if(isset($_GET['slideshow']))
		{
			return esc_attr($_GET['slideshow']);
		}

		// Otherwise get all slideshows
		$slideshow = get_terms('rg_slides_slideshow', 'hide_empty=0');

		// If no slideshow are set, slug is new
		if(empty($slideshow))
		{
			return $default;
		}
		// Else the slideshow is the first slideshow of all slideshows
		else
		{
			return $slideshow[0]->slug;
		}
	}
}

/**
 * Check the current version of the Wordpress Installation
 * against a given version number
 *
 * @param string  $version   The version to check against
 * @param string  $operator  The operator to use for the comparison
 */
if( ! function_exists('is_version'))
{
	function is_version($version = '3.8', $operator = '>=')
	{
		// Get global $wp_version variable
		global $wp_version;

		// Compare version with supplied version
		return version_compare($wp_version, $version, $operator);
	}
}