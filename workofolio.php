<?php
/*
Plugin Name: Workofolio
Description: Let's you list all  your works in a simple organized way. You can add new images and videos to each listed work! Also, associate a work with one or more service and clients.
Version: 0.1
Author: Heldrida
Author URI: http://macacosimao.com
License: MIT (http://www.opensource.org/licenses/mit-license.php)
*/

define('WRKFL_WEBSITE_URL', site_url().'/');
define('WRKFL_WEBSITE_ADMIN_URL', admin_url());
define('WRKFL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('WRKFL_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

register_activation_hook( __FILE__, 'WRKFL_installation_dependencies' );
register_deactivation_hook( __FILE__, 'WRKFL_deactivation_dependencies' );
add_filter("manage_edit-hwc-cpt-works_columns", "cpt_works_display_columns");

add_action( 'init', 'WRKFL_initialize' );
add_action( 'manage_hwc-cpt-works_posts_custom_column' , 'custom_columns' );
add_action('admin_menu', 'cpt_works_add_box');
add_action('admin_enqueue_scripts', 'hwc_admin_scripts');
add_action('wp_enqueue_scripts', 'hwc_wp_style');
add_action( 'save_post', 'hwc_admin_save_post' );

if(! function_exists('WRKFL_getWorksList')) {

	/**
	 * Get list of works
	 * @return array
	 */
	function WRKFL_getWorksList(){

		global $post;

		$myposts= get_posts( array('numberposts' => -1, 'orderby' => 'post_date', 'order' => 'DESC', 'category' => 3 ) );
		$data = array();
		$i = 0;

		foreach( $myposts As $post ){

				$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');

				$data[] = array(
					'thumb' => $thumb[0],
					'client' => get_post_meta($post->ID, 'client', true),
					'content' => $post->post_content,
					'title' => $post->post_title,
					'perm_link' => $post->guid
				);

		};

		return isset($data) && ! empty($data) ? $data : false;

	};	
};
if(! function_exists('WRKFL_initialize')){
	function WRKFL_initialize(){

		register_post_type( 'hwc-cpt-works',
			array(
				'labels' => array(
					'menu_name' => 'Howoco Works',
					'name' => __( 'Works' ),
					'singular_name' => __( 'Work' )
				),
			'public' => true,
			'has_archive' => true,
			'supports' => array('title', 'thumbnail', 'editor', 'page-attributes')
			)
		);		
		register_taxonomy('hwc-work-services', array('hwc-cpt-works'), array('hierarchical' => true, 'label' => 'Services', 'singular_label' => 'Service', 'rewrite' => true));

		register_taxonomy('hwc-work-clients', array('hwc-cpt-works'), array('hierarchical' => true, 'label' => 'Clients', 'singular_label' => 'Client', 'rewrite' => true));
	};
};
if(! function_exists('cpt_works_display_columns')){
	/*
	 *	Set the columns in table
	 */
	function cpt_works_display_columns($cols){
	
		unset($cols);
		
		return array(
					'title'			=> __('Title'),
					'client'		=> __('Client'),
					'services'		=> __('Services')
				);
	
	};
};
if(!function_exists('custom_columns')){
	function custom_columns( $column ) {

		switch ( $column ) {

			case 'client':

				$clients = get_the_terms( $postId, 'hwc-work-clients' );

				if( ! $clients ){
					echo '- - - -';
					return;
				}

				$client .= '';
				foreach ($clients as $k => $v) {					
					$client .= $v->name.',';										
				}
				echo substr( $client, 0, -1) ? substr( $client, 0, -1) : '- - - -';
					
			break;

			case 'services':

				$services 	= get_the_terms( $postId, 'hwc-work-services' );

				if( ! $services ){
					echo '- - - -';
					return;
				}

				$serv .= '';
				foreach ($services as $k => $v) {				
					$serv .= $v->name.',';										
				}
				echo substr( $serv, 0, -1) ? substr( $serv, 0, -1) : '- - - -';
									
			break;

		};
	};	
};
if(! function_exists('cpt_works_meta_fields_nodes')){
	/*
	 * Html nodes to use for Custom Post Type
	 */
	function cpt_works_meta_fields_nodes(){
	  
		global $post;
	 
		// Use underscore to prevent showing up in Custom Field Section		
		$hwc_work_images = get_post_meta($post->ID, 'hwc-work-images', TRUE);

		$i = 1;
		$list = '<div id="hwc-works-list-wrp">';
		$list .= '<ul id="hwc-works-list" class="hwc-wrk-img-uploader" style="width:500px; height:auto; float:left; clear:both;">';

		if( isset($hwc_work_images) && is_array( $hwc_work_images ) ){

			foreach( $hwc_work_images As $k => $v ){
				
				$list .= '	<li>
									<div class="hwc-work-remove">remove</div>
									<img src="'.$v['image'].'" alt="" />
									<div class="hwc-work-editor-wrp">

										<div class="title-editor">
											<input type="text" name="hwc-work-images['.($i).'][title]" value="'.( empty($v['title']) ? 'Your image title...' : $v['title'] ).'" />
										</div>
										<div class="url-editor">
											<input type="text" name="hwc-work-images['.($i).'][link]" value="'.( isset($v['link']) ? $v['link'] : '#' ).'" />											
										</div>

									</div>
									<input type="hidden" name="hwc-work-images['.($i).'][image]" class="hwc-image-file" value="'.$v['image'].'" />
							</li>';
							
				$i++;
			};
			
		} else {
			
			$list .= '	<li>
							<div class="no-img-yet">
								<h1>Add images</h1>
								<p>Please click in add more button, to add some images.</p>
							</div>
						</li>';

		};

		$list .= '</ul>';
		$list .= '<div class="add-img-wrp" style="float:left; clear:both;">
					<a href="#" class="button hwc-add-image" style="color:#888; font-size: 30px; text-decoration: none;">+ add more</a></div>';
		$list .= '</div>';

		echo $list;

	};	
};
if(! function_exists('cpt_works_meta_fields_nodes_b')){
	function cpt_works_meta_fields_nodes_b(){
		
		global $post;

		$hwc_work_videos = get_post_meta($post->ID, 'hwc-work-videos', TRUE);


		$node = '<div id="work-meta-wrp">';
		$node .= '<table>';

		$node .= '<tr>';
		$node .= '<td>';
		/* s: block */
		$node .= '<div class="hwc-video-wrp">';
		$node .= '<h2>FLASH</h2>';
		$node .= '<div class="row">';
		$node .= '<label for="hwc-work-videos[flv][title]">Title:</label><input class="hwc-work-meta-field" type="text" name="hwc-work-videos[flv][title]" value="'.( isset($hwc_work_videos['flv']['title']) && ! empty( $hwc_work_videos['flv']['title'] ) ? $hwc_work_videos['flv']['title'] : null).'" />';
		$node .= '</div>';
		$node .= '<div class="row">';
		$node .= '<label for="hwc-work-videos[flv][url]">*.FLV URL:</label><input class="hwc-work-meta-field hwc-work-video-add" type="text" name="hwc-work-videos[flv][url]" value="'.( isset($hwc_work_videos['flv']['url']) ? $hwc_work_videos['flv']['url'] : null).'" />';
		$node .= '</div>';
		$node .= '<div class="row">';
		$node .= '<label for="hwc-work-videos[flv][published]">Published ?</label><input class="hwc-work-meta-field checkbox" type="checkbox" name="hwc-work-videos[flv][published]" value="1" '.( isset($hwc_work_videos['flv']['url']) && ! empty($hwc_work_videos['flv']['url']) && isset($hwc_work_videos['flv']['published']) && $hwc_work_videos['flv']['published'] == 1 ? 'checked="checked"' : null ).' />';
		$node .= '</div>';
		$node .= '</div>';
		/* e: block */
		$node .= '</td>';
		$node .= '</tr>';

		$node .= '<tr>';
		$node .= '<td>';
		/* s: block */
		$node .= '<div class="hwc-video-wrp">';
		$node .= '<h2>EXTERNAL</h2>';
		$node .= '<div class="row">';
		$node .= '<label for="hwc-work-videos[external][title]">Title:</label><input class="hwc-work-meta-field" type="text" name="hwc-work-videos[external][title]" value="'.( isset($hwc_work_videos['external']['title']) && ! empty( $hwc_work_videos['external']['title'] ) ? $hwc_work_videos['external']['title'] : null).'" />';
		$node .= '</div>';
		$node .= '<div class="row embed">';
		
		$node .= '<label for="hwc-work-videos[external][url]">Embed Code:</label>
		<textarea class="hwc-work-meta-field" type="text" name="hwc-work-videos[external][embed]">
		'.( isset($hwc_work_videos['external']['embed']) && ! empty( $hwc_work_videos['external']['embed'] ) ? $hwc_work_videos['external']['embed'] : null).'
		</textarea>';
	
		$node .= '</div>';
		$node .= '<div class="row">';
		$node .= '<label for="hwc-work-videos[external][published]">Published ?</label><input class="hwc-work-meta-field checkbox" type="checkbox" name="hwc-work-videos[external][published]" value="1" '.( isset($hwc_work_videos['external']['embed']) && ! empty($hwc_work_videos['external']['embed']) && isset($hwc_work_videos['external']['published']) && $hwc_work_videos['external']['published'] == 1 ? 'checked="checked"' : null ).' />';
		$node .= '</div>';
		$node .= '</div>';
		/* e: block */
		$node .= '</td>';
		$node .= '</tr>';

		$node .= '</table>';
		$node .= '</div>';

		echo $node;

	};
};
if(! function_exists('cpt_works_add_box')){
	function cpt_works_add_box(){
		if( is_admin()){
			add_meta_box('cpt-works-config-parameters', 'Work image gallery', 'cpt_works_meta_fields_nodes', 'hwc-cpt-works', 'normal', 'low');

			add_meta_box('cpt-works-config-parameters-b', 'Work video', 'cpt_works_meta_fields_nodes_b', 'hwc-cpt-works', 'normal', 'high');	
					
		};
	}
};
if(! function_exists('hwc_admin_scripts')){
	function hwc_admin_scripts() {

			$template_url = get_bloginfo('template_directory');

		    wp_register_script( 'hwc-works-script', WRKFL_PLUGIN_URL.'/public/js/hwc-works.js');
		    wp_enqueue_script( 'hwc-works-script' );

			wp_register_style('hwc-works-style',  WRKFL_PLUGIN_URL.'/public/css/hwc-works.css');
			wp_enqueue_style( 'hwc-works-style');
	    
	};
};
if(! function_exists('hwc_wp_style')){
	function hwc_wp_style(){
		wp_register_style('hwc-works-frontend-style',  WRKFL_PLUGIN_URL.'/public/css/hwc-works.css');
		wp_enqueue_style( 'hwc-works-frontend-style');

	    wp_register_script( 'hwc-works-script', WRKFL_PLUGIN_URL.'/public/js/hwc-works.js');
	    wp_enqueue_script( 'hwc-works-script' );

	};
};
if(! function_exists('hwc_admin_save_post')){
	function hwc_admin_save_post(){
		
		global $post;

		if( is_admin() ){

			if( isset($_POST['hwc-work-images']) && is_array($_POST['hwc-work-images']) ){

				delete_post_meta($post->ID, 'hwc-work-images');
				
				add_post_meta($post->ID, 'hwc-work-images', $_POST['hwc-work-images']);

			};

			if( isset($_POST['hwc-work-videos']) && is_array($_POST['hwc-work-videos']) ){
				
				delete_post_meta($post->ID, 'hwc-work-videos');
				
				add_post_meta($post->ID, 'hwc-work-videos', $_POST['hwc-work-videos']);

			};

		};

	};
};
if(! function_exists('hwc_get_post_taxonimies')){
	function hwc_get_post_taxonimies($postId, $opt) {
		$data = get_the_terms($postId, $opt);
		
		$names = array();
		foreach($data as $item) {
			$names[] = $item->name;
		}

		return implode(',', $names);
	};
};
