<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

class RG_Slider_Optionspage {

	public function __construct()
	{
		add_action('admin_menu', array($this, 'setup_options_page'));
	}

	//------------------------------------------------------------------------

	public function setup_options_page()
	{
		// Add a new sub menu page
		add_submenu_page('rg-slider', __('Options', 'rg-slider'), __('Options', 'rg-slider'), 'administrator', 'rg-slider-options', array($this, 'display'));
	}

	//------------------------------------------------------------------------

	public function display()
	{
		$slideshows = get_terms('rg_slides_slideshow', 'hide_empty=0');

		$show_slug = get_show_slug('default');

		$options = get_option('rg_slider_' . $show_slug);

		?>
			<div class="wrap">
				<?php screen_icon('rg-slider');?>
				<h2>RG Slider - <?php _e('Options', 'rg-slider');?></h2>

				<h2 class="nav-tab-wrapper">
					<?php 
						if($slideshows) : foreach($slideshows AS $show) :
					?>
						<a href="<?php menu_page_url('rg-slider-options');?>&slideshow=<?=$show->slug;?>" class="nav-tab <?php echo ($show_slug == $show->slug) ? 'nav-tab-active' : '';?>"><?=$show->name;?></a>
					<?php endforeach; endif;?>
					<a href="<?php menu_page_url('rg-slider-options');?>&slideshow=default" class="nav-tab <?php echo ($show_slug == 'default') ? 'nav-tab-active' : '';?>"><?php _e('Default', 'rg-slider');?></a>
				</h2>

				<?php if($show_slug == 'default') :?>

					<h3><?php _e('Default settings', 'rg-slider');?></h3>
					<p><?php _e('Below you can set the default settings for RG Slider. All new slideshows will inherit these settings.', 'rg-slider');?></p>
				
				<?php else :?>

					<?php $slideshow = get_term_by('slug', $show_slug, 'rg_slides_slideshow');?>
					<h3><?php printf(__('Settings for "%1$s"', 'rg-slider'), $slideshow->name);?></h3>
					<p><?php printf(__('Below you can set the settings for "%1$s".', 'rg-slider'), $slideshow->name);?></p>
				
				<?php endif;?>

				<hr class="seperator">

				<form method="post" action="<?php echo admin_url('admin.php');?>">
					<?php wp_nonce_field('update_slideshow','update_slideshow_nonce'); ?>
					<input type="hidden" name="action" value="update_slideshow">
					<input type="hidden" name="show_slug" value="<?php echo $show_slug;?>">

					<h3><?php _e('Slideshow Settings', 'rg-slider');?></h3>

					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_slideshow_delay"><?php echo sprintf('%1$s <small>(%2$s)</small>', __('Delay', 'rg-slider'), __('in milliseconds', 'rg-slider'));?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_slideshow_delay" name="slideshow_delay" class="regular-text" value="<?php echo $options['delay'];?>">
								</td>
							</tr>

						</tbody>
					</table>

					<h3><?php _e('Image Settings', 'rg-slider');?></h3>

					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_width"><?php echo sprintf('%1$s <small>(%2$s)</small>', __('Image Width', 'rg-slider'), __('in pixels', 'rg-slider'));?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_width" name="image_width" class="regular-text" value="<?php echo $options['slide_width'];?>">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_height"><?php echo sprintf('%1$s <small>(%2$s)</small>', __('Image Height', 'rg-slider'), __('in pixels', 'rg-slider'));?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_height" name="image_height" class="regular-text" value="<?php echo $options['slide_height'];?>">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_quality"><?php echo sprintf('%1$s <small>(%2$s)</small>', __('Quality', 'rg-slider'), __('in percent', 'rg-slider'));?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_quality" name="image_quality" class="regular-text" value="<?php echo $options['quality'];?>">
								</td>
							</tr>

						</tbody>
					</table>

					<h3><?php _e('Navigation Settings', 'rg-slider');?></h3>

					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_navigation"><?php _e('Nagivation', 'rg-slider');?></label>
								</th>
								<td>
									<select id="rg_slider_navigation" name="navigation">
										<option value="1" <?php selected(1, $options['navigation']);?>><?php _e('Enable', 'rg-slider');?></option>
										<option value="0" <?php selected(0, $options['navigation']);?>><?php _e('Disable', 'rg-slider');?></option>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_navigation_next_prev"><?php _e('Next & Previous Buttons', 'rg-slider');?></label>
								</th>
								<td>
									<select id="rg_slider_navigation_next_prev" name="navigation_next_prev">
										<option value="1" <?php selected(1, $options['next_prev']);?>><?php _e('Enable', 'rg-slider');?></option>
										<option value="0" <?php selected(0, $options['next_prev']);?>><?php _e('Disable', 'rg-slider');?></option>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_navigation_next"><?php _e('Next Text', 'rg-slider');?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_navigation_next" name="navigation_next" class="regular-text" value="<?php echo $options['next'];?>">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_navigation_prev"><?php _e('Previous Text', 'rg-slider');?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_navigation_prev" name="navigation_prev" class="regular-text" value="<?php echo $options['prev'];?>">
								</td>
							</tr>

						</tbody>
					</table>

					<h3><?php _e('Caption Settings', 'rg-slider');?></h3>

					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_caption_opacity"><?php echo sprintf('%1$s <small>(%2$s)</small>', __('Caption Opacity', 'rg-slider'), __('in percent', 'rg-slider'));?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_caption_opacity" name="caption_opacity" class="regular-text" value="<?php echo $options['caption_opacity'];?>">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_caption_bgcolor"><?php _e('Caption Background', 'rg-slider');?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_caption_bgcolor" name="caption_bgcolor" class="regular-text" value="<?php echo $options['caption_bgcolor'];?>">
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="rg_slider_image_caption_textcolor"><?php _e('Caption Text', 'rg-slider');?></label>
								</th>
								<td>
									<input type="text" id="rg_slider_image_caption_textcolor" name="caption_textcolor" class="regular-text" value="<?php echo $options['caption_textcolor'];?>">
								</td>
							</tr>

						</tbody>
					</table>

					<?php submit_button();?>
					
				</form>

			</div>
		<?php
	}

}