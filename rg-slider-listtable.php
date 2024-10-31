<?php if( ! defined('RG_SLIDER')) die('You can\'t access this file directly');

if( ! class_exists('WP_List_Table'))
{
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class RG_Slider_Listtable extends WP_List_Table {

	private $_per_page = 5;
	private $_show_slug;

	public function __construct($show_slug)
	{
		global $status, $page;
		$this->_show_slug = $show_slug;

		parent::__construct(array(
			'singular'  => 'slide',
			'plural'    => 'slides',
			'ajax'      => FALSE
		));
	}

	//------------------------------------------------------------------------

	public function get_columns()
	{
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __('Name', 'rg-slider'),
			'image'    => __('Image', 'rg-slider'),
			'caption'  => __('Caption', 'rg-slider'),
			'link' => __('Link', 'rg-slider')
		);

		return $columns;
	}

	//------------------------------------------------------------------------

	public function get_sortable_columns()
	{
		// $sortable_columns = array(
		// 	'title'     => array('title', FALSE),
		// 	'caption'   => array('caption', FALSE),
		// 	'link'      => array('link', FALSE)
		// );

		// return $sortable_columns;
		return array();
	}


	//------------------------------------------------------------------------

	public function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID
		);
	}

	//------------------------------------------------------------------------

	public function no_items()
	{
		_e('No slides found for this slideshow', 'rg-slider');
	}

	//------------------------------------------------------------------------

	public function column_default($item, $column_name)
	{
		switch($column_name){
			case 'title':
				return $item->post_title;

			case 'image':
				$upload = wp_upload_dir();
				$slide_meta = get_post_meta($item->ID, 'slide_meta');
				return '<img src="' . $upload['baseurl'] . '/rg-slider/' . $slide_meta[0]['slide_name'] . '?v=' . time() . '" alt="' . $item->post_title . '" class="slideshow_thumbnail">';

			case 'caption':
				return '<p>' . $item->post_content . '</p>';

			case 'link':
				$slide_meta = get_post_meta($item->ID, 'slide_meta');
				return '<p>' . $slide_meta[0]['slide_link'] . '</p>';

			default:
				return;
		}
	}

	//------------------------------------------------------------------------

	public function column_title($item)
	{
		$slideshow = wp_get_post_terms($item->ID, 'rg_slides_slideshow');

		$actions['edit'] = '<a href="' . menu_page_url('rg-slider-crop-page', FALSE) . '&slide=' . $item->ID . '">' . __('Edit', 'rg-slider') . '</a>';

		if($item->post_status == 'publish')
		{
			$actions['disable'] = '<a href="' . menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug . '&action=disable&slide=' . $item->ID . '">' . __('Disable', 'rg-slider') . '</a>';
		}
		else
		{
			$actions['enable'] = '<a href="' . menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug . '&action=enable&slide=' . $item->ID . '">' . __('Enable', 'rg-slider') . '</a>';
		}

		$actions['delete'] = '<a href="' . menu_page_url('rg-slider', FALSE) . '&action=delete&slide=' . $item->ID . '">' . __('Delete', 'rg-slider') . '</a>';

		$actions['moveup'] = '<a href="' . menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug . '&action=moveup&slide=' . $item->ID . '">' . __('Move Up', 'rg-slider') . '</a>';
		$actions['movedown'] = '<a href="' . menu_page_url('rg-slider', FALSE) . '&slideshow=' . $slideshow[0]->slug . '&action=movedown&slide=' . $item->ID . '">' . __('Move Down', 'rg-slider') . '</a>';

		$post_state = ($item->post_status !== 'publish') ? ' - <span class="post-state">' . __('Disabled', 'rg-slider') . '</span>' : '';

		return sprintf('<strong><a class="row-title" title="Edit “%1$s”" href="%2$s">%1$s</a>%3$s</strong>%4$s',
			$item->post_title,
			menu_page_url('rg-slider-crop-page', FALSE) . '&slide=' . $item->ID,
			$post_state,
			$this->row_actions($actions)
		);
	}

	//------------------------------------------------------------------------

	public function get_bulk_actions()
	{
		$actions = array(
			'delete' => __('Delete', 'rg-slider'),
			'enable' => __('Enable', 'rg-slider'),
			'disable' => __('Disable', 'rg-slider')
		);

		return $actions;
	}

	//------------------------------------------------------------------------

	public function process_bulk_action()
	{
		switch($this->current_action())
		{
			case 'delete':
				RG_Slider_Slide::delete($_REQUEST['slide']);

				$slide_id = (is_array($_REQUEST['slide'])) ? $_REQUEST['slide'][0] : $_REQUEST['slide'];
				break;

			case 'enable':
				RG_Slider_Slide::enable($_REQUEST['slide']);

				$slide_id = (is_array($_REQUEST['slide'])) ? $_REQUEST['slide'][0] : $_REQUEST['slide'];
				break;

			case 'disable':
				RG_Slider_Slide::disable($_REQUEST['slide']);

				$slide_id = (is_array($_REQUEST['slide'])) ? $_REQUEST['slide'][0] : $_REQUEST['slide'];
				break;

			case 'moveup':
				RG_Slider_Slide::moveup($_REQUEST['slide']);

				break;

			case 'movedown':
				RG_Slider_Slide::movedown($_REQUEST['slide']);

				break;
		}
	}

	//------------------------------------------------------------------------

	public function prepare_items()
	{
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = get_posts(array(
			'post_type' => 'rg_slides',
			'post_status' => 'any',
			'meta_key' => 'slide_order',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'rg_slides_slideshow',
					'field' => 'slug',
					'terms' => $this->_show_slug
				)
			)
		));

		// Get the current page
		$current_page = $this->get_pagenum();

		// Get total items
		$total_items = count($data);

		$data = array_slice($data, (($current_page - 1) * $this->_per_page), $this->_per_page);

		$this->items = $data;

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $this->_per_page,
			'total_pages' => ceil($total_items / $this->_per_page)
		));
	}

	//------------------------------------------------------------------------

	public function pagination($which) {
		ob_start();
		parent::pagination($which);
		$pagination = ob_get_contents();
		ob_end_clean();

		$pagination = str_replace('item', 'slide', $pagination);
		$pagination = str_replace('items', 'slides', $pagination);

		echo $pagination;
	}
}