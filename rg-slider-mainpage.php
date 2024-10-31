<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Mainpage {

	public function __construct()
	{
		add_action('admin_menu', array($this, 'setup_main_page'));
	}

	//------------------------------------------------------------------------

	public function setup_main_page()
	{
		// Add menu page
		add_menu_page('RG Slider', 'RG Slider', 'administrator', 'rg-slider', array($this, 'display'), 'div', 81);
	}

	//------------------------------------------------------------------------

	public function display()
	{
		// Get the slideshow slug
		$show_slug = get_show_slug();

		// Get all slideshows
		$slideshows = get_terms('rg_slides_slideshow', 'hide_empty=0');

		// If the user pushed the cancel button
		if(isset($_GET['action']) && $_GET['action'] == 'cancel_upload')
		{
			// Get image url
			$image = get_transient('rg-slider-new-slide');

			// Get the filename
			$filename = pathinfo($image, PATHINFO_BASENAME);

			// Get upload folder settings
			$upload = wp_upload_dir();

			// Delete images
			@unlink($upload['basedir'] . '/rg-slider/' . $filename);
			@unlink($upload['basedir'] . '/rg-slider/' . set_orig($filename));

			// Remove transients
			delete_transient('rg-slider-new-slide');
			delete_transient('rg-slider-new-slide-slug');
		}

		?>

			<div class="wrap">

				<?php screen_icon('rg-slider');?>
				<h2>RG Slider</h2>
				
				<!-- Page heading and text -->
				<h3><?php _e('Upload a new image', 'rg-slider');?></h3>
				<p><?php _e('Select an image in the file upload input below and click "Add New" to upload an add a new slide.', 'rg-slider');?></p>

				<!-- Upload form -->
				<form enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin.php');?>">
					<?php wp_nonce_field('add_new_slide','add_new_slide_nonce'); ?>
					<input type="hidden" name="action" value="upload_slide">
					<input type="hidden" name="show_slug" value="<?php echo $show_slug;?>">
					<input type="file" name="add_new_slide" id="add_new_slide">
					<input type="submit" name="add_new_slide_submit" id="add_new_slide_submit" value="<?php _e('Add New', 'rg-slider');?>" class="button-primary action">
				</form>

				<hr class="seperator">

				<h2 class="nav-tab-wrapper">
					<?php 
						if($slideshows) : foreach($slideshows AS $show) :
					?>
						<a href="<?php menu_page_url('rg-slider');?>&slideshow=<?=$show->slug;?>" class="nav-tab <?php echo ($show_slug == $show->slug) ? 'nav-tab-active' : '';?>"><?=$show->name;?></a>
					<?php endforeach; endif;?>
					<a href="<?php menu_page_url('rg-slider');?>&slideshow=new" class="nav-tab <?php echo ($show_slug == 'new') ? 'nav-tab-active' : '';?>">+</a>
				</h2>

				<!-- If the slug is 'new', than display the new slideshow page -->
				<?php if($show_slug === 'new') :?>

					<h3><?php _e('Add a new slideshow', 'rg-slider');?></h3>
					<p><?php _e('Fill in the desired slideshow name and click "Add New" to add a new slideshow.', 'rg-slider');?></p>

					<form method="post" action="<?php echo admin_url('admin.php');?>">
						<?php wp_nonce_field('create_slideshow','create_slideshow_nonce'); ?>
						<input type="hidden" name="action" value="create_slideshow">
						<input type="text" name="create_slideshow" id="create_slideshow">
						<input type="submit" name="create_slideshow_submit" id="create_slideshow_submit" value="<?php _e('Add New', 'rg-slider');?>" class="button-primary action">
					</form>

				<!-- Else, display the specified slideshow overview page -->
				<?php else :?>

					<?php 
						$slideshow = get_term_by('slug', $show_slug, 'rg_slides_slideshow');
						$slides = new WP_Query(array(
							'post_type' => 'rg_slides',
							'tax_query' => array(
								array(
									'taxonomy' => 'rg_slides_slideshow',
									'field' => 'slug',
									'terms' => $show_slug
								)
							)
						));

						$shortcode = '<code class="code_snippet">[rg-slider show=' . $slideshow->slug . ']</code>';
						$php_code = '<code class="code_snippet">' . htmlspecialchars("<?php if(function_exists('rg_slider')){echo rg_slider('" . $slideshow->slug . "');}?>") . '</code>';
					?>

					<h3>
						Slideshow: "<?php echo $slideshow->name;?>" 
						<a href="<?php menu_page_url('rg-slider-options');?>&slideshow=<?php echo $slideshow->slug;?>" class="slideshow_options"><?php _e('Slideshow options', 'rg-slider');?></a>

						<form method="post" action="<?php echo admin_url('admin.php');?>" class="delete_slideshow_form">
							<?php wp_nonce_field('delete_slideshow','delete_slideshow_nonce'); ?>
							<input type="hidden" name="action" value="delete_slideshow">
							<input type="hidden" name="slideshow_id" value="<?php echo $slideshow->term_id;?>">
							<input type="submit" name="delete_slideshow_submit" class="delete_slideshow" id="delete_slideshow_submit" value="<?php _e('Delete slideshow', 'rg-slider');?>">
						</form>

					</h3>
					<p>
						<?php printf(__('You can add this slideshow to a page or post with the following shortcode %1$s or the following PHP code %2$s.', 'rg-slider'), $shortcode, $php_code);?>
					</p>

					<!-- Bulk action form -->
					<form method="post" action="<?php menu_page_url('rg-slider');?>&slideshow=<?php echo $slideshow->slug;?>">

						<?php 
							$list_table = new RG_Slider_Listtable($show_slug);

							$list_table->prepare_items();

							$list_table->display();
						?>

					</form>

				<?php endif;?>

			</div>

		<?php
	}
	
	//------------------------------------------------------------------------

	// public static function show_slug()
	// {
	// 	// If a slideshow slug is set in $_GET we want to show that slideshow
	// 	if(isset($_GET['slideshow']))
	// 	{
	// 		return esc_attr($_GET['slideshow']);
	// 	}

	// 	// Otherwise get all slideshows
	// 	$slideshow = get_terms('rg_slides_slideshow', 'hide_empty=0');

	// 	// If no slideshow are set, slug is new
	// 	if(empty($slideshow))
	// 	{
	// 		return 'new';
	// 	}
	// 	// Else the slideshow is the first slideshow of all slideshows
	// 	else
	// 	{
	// 		return $slideshow[0]->slug;	
	// 	}
	// }


}