<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Croppage {

	public function __construct()
	{
		add_action('admin_menu', array($this, 'setup_crop_page'));
	}

	//------------------------------------------------------------------------

	public function setup_crop_page()
	{
		add_submenu_page(NULL, 'Crop Page', 'Crop Page', 'administrator', 'rg-slider-crop-page', array($this, 'display'));
	}

	//------------------------------------------------------------------------

	public function display()
	{
		$slideshows = get_terms('rg_slides_slideshow', 'hide_empty=0');
		if(isset($_GET['slide']))
		{
			$slide = get_post(esc_attr($_GET['slide']));

			$upload = wp_upload_dir();
			$slide_url = $upload['baseurl'] . '/rg-slider/' . set_orig($slide->post_title);
			$slide_meta = get_post_meta($slide->ID, 'slide_meta');
			$slide_name = $slide_meta[0]['slide_name'];
			$slide_x = $slide_meta[0]['slide_x'];
			$slide_y = $slide_meta[0]['slide_y'];
			$slide_x2 = $slide_meta[0]['slide_x2'];
			$slide_y2 = $slide_meta[0]['slide_y2'];
			$slide_w = $slide_meta[0]['slide_w'];
			$slide_h = $slide_meta[0]['slide_h'];
			$slide_link = $slide_meta[0]['slide_link'];
			$slide_caption = $slide->post_content;

			$slideshow = wp_get_post_terms($slide->ID, 'rg_slides_slideshow');
			$show_slug = $slideshow[0]->slug;
		}
		else
		{
			$slide_x = 0;
			$slide_y = 0;
			$slide_x2 = 100;
			$slide_y2 = 100;
			$slide_w = 100;
			$slide_h = 100;
			$slide_link = '';
			$slide_caption = '';
			if(get_transient('rg-slider-new-slide'))
			{
				$slide_url = get_transient('rg-slider-new-slide');
				$show_slug = get_transient('rg-slider-new-slide-slug');
			}
		}

		?>
			<div class="wrap">
				<?php screen_icon('rg-slider');?>
				<h2>RG Slider</h2>

				<!-- Page heading and text -->
				<h3><?php _e('Edit image', 'rg-slider');?></h3>
				<p><?php _e('Crop the image and optionally set a caption and link.', 'rg-slider');?></p>

				<hr class="seperator">

				<!-- Slide form -->
				<form method="post" action="<?php echo admin_url('admin.php');?>">

					<!-- Set some hidden inputs, mainly for the crop process -->
					<?php if(isset($slide)) : ?>
						<input type="hidden" name="action" value="update_slide">
						<input type="hidden" name="slide_name" value="<?php echo $slide_name;?>">
					<?php else :?>
						<input type="hidden" name="action" value="create_slide">
					<?php endif;?>
					<input type="hidden" name="new_image_x" id="new_image_x" value="<?php echo $slide_x;?>">
					<input type="hidden" name="new_image_y" id="new_image_y" value="<?php echo $slide_y;?>">
					<input type="hidden" name="new_image_x2" id="new_image_x2" value="<?php echo $slide_x2;?>">
					<input type="hidden" name="new_image_y2" id="new_image_y2" value="<?php echo $slide_y2;?>">
					<input type="hidden" name="new_image_w" id="new_image_w" value="<?php echo $slide_w;?>">
					<input type="hidden" name="new_image_h" id="new_image_h" value="<?php echo $slide_h;?>">
					<input type="hidden" name="new_image_url" id="new_image_url" value="<?php echo $slide_url;?>">
					<?php if(isset($slide)) : ?>
						<input type="hidden" name="slide_id" id="slide_id" value="<?php echo $slide->ID;?>">
					<?php endif;?>

					<!-- Image container -->
					<div id="new_image_image_container">
						<img src="<?php echo $slide_url;?>?v=<?php echo time();?>" alt="<?php _e('New Slide', 'rg-slider');?>" id="new_slide_image">
					</div>

					<!-- Caption wp_editor container -->
					<div id="new_image_form_container">
						<h3 <?php echo is_version('3.7', '>') ? 'id="caption_label"' : '';?>><?php _e('Add a caption', 'rg-slider');?></h3>
						<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv';?>" class="postarea">
							<?php
								// Add the wp_editor, set caption as content
								// And don't show media buttons. And we want a very
								// simple layout
								wp_editor($slide_caption, 'new_image_caption', array(
									'media_buttons' => FALSE,
									'textarea_rows' => 4,
									'tabindex' => 1,
									'teeny' => TRUE
								));
							?>
						</div>

						<!-- Link input container -->
						<h3><?php _e('Add a link', 'rg-slider');?></h3>
						<div id="titlediv">
							<input type="text" name="new_image_link" id="new_image_link" class="widefat" value="<?php echo $slide_link;?>" tab-index="2">
						</div>

						<!-- Link input container -->
						<h3><?php _e('Select the slideshow', 'rg-slider');?></h3>
						<div id="slideshowdiv">
							<select name="new_image_slideshow" id="new_image_slideshow" class="widefat" tab-index="3">
								<?php foreach($slideshows AS $show) :?>
									<option value="<?php echo $show->slug;?>" <?php selected($show_slug, $show->slug);?>><?php echo $show->name;?></option>
								<?php endforeach;?>
							</select>
						</div>

						<!-- And finally the submit (and cancel) button -->
						<p class="submit">
							<input type="submit" name="add_new_slide_submit" id="add_new_slide_submit" value="<?php (isset($slide)) ? _e('Edit slide', 'rg-slider') : _e('Save slide', 'rg-slider');?>" class="button-primary action">
							<a class="preview button" href="<?php menu_page_url('rg-slider');?><?php echo ( ! isset($slide)) ? '&action=cancel_upload' : '';?>"><?php _e('Cancel', 'rg-slider');?></a>
						</p>
					</div>

				</form>

			</div>
		<?php
	}

}