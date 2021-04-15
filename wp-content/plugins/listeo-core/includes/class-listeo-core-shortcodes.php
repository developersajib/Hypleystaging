<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Listeo_Core_Shortcodes class.
 */
class Listeo_Core_Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_shortcode( 'listings', array( $this, 'show_listings' ) );
		add_filter('listeo_core_output_defaults',array( $this, 'add_custom_listing_atts' ) );
		//add_filter('listeo_core_output_defaults',array( $this, 'add_custom_listing_atts' ) );
	
	}


	function add_custom_listing_atts($atts) {
	  # this filter should only run once (first use on page)
	  $available_query_vars = Listeo_Core_Search::build_available_query_vars();

	  foreach ($available_query_vars as $key => $meta_key) {
	  	$atts[$meta_key] = '';
	  }
	  $taxonomy_objects = get_object_taxonomies( 'listing', 'objects' );
		foreach ($taxonomy_objects as $tax) {
		  	$atts['tax-'.$tax->name] = '';
		  }

	  return $atts;
	}

	public function show_listings( $atts = array() ) {

		extract( $atts = shortcode_atts( apply_filters( 'listeo_core_output_defaults', array(
			
			'style'						=> 'list', //grid, grid-compact
			'layout_switch'				=> 'off',
			'list_top_buttons'			=> 'filters|order|layout|radius', //filters|order|layout
			'per_page'                  => get_option('listeo_listings_per_page',10),
			'orderby'                   => '',
			'order'                     => '',
			'keyword'                   => '',
			'location'                   => '',
			'search_radius'             => '',
			'radius_type'               => '',
			'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
			'custom_class'				=> '',
			'grid_columns'				=> '2',
			'in_rows'					=> '',
			'ajax_browsing'				=> get_option('listeo_ajax_browsing'),
			'from_vs'				=> '',
		) ), $atts ) );
		  
		ob_start();
		$template_loader = new Listeo_Core_Template_Loader;
		// Get listings query
		$ordering_args = Listeo_Core_Listing::get_listings_ordering_args( $orderby, $order );
 
		if ( ! is_null( $featured ) ) {

			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;

		}

		if($from_vs=='yes'){
			$list_top_buttons = str_replace(',','|',$list_top_buttons);
		}

		$get_listings = array_merge($atts,array(
				'posts_per_page'    => $per_page,
				'orderby'           => $ordering_args['orderby'],
				'order'             => $ordering_args['order'],
				'keyword_search'   	=> $keyword,
				'location_search'   => $location,
				'search_radius'   	=> $search_radius,
				'radius_type'   	=> $radius_type,
				'listeo_orderby'   	=> $orderby,
				
			));
		
		$get_listings['featured'] = $featured;
		
		$listeo_core_query = Listeo_Core_Listing::get_real_listings( apply_filters( 'listeo_core_output_defaults_args', $get_listings ));
		$listeo_verify_query = Listeo_Core_Listing::get_verify_listings( apply_filters( 'listeo_core_output_defaults_args', $get_listings ));
		$listeo_unverify_query = Listeo_Core_Listing::get_unverify_listings( apply_filters( 'listeo_core_output_defaults_args', $get_listings ));

		?>

			<div class="row margin-bottom-25">

				<?php do_action( 'listeo_before_archive', $style, $list_top_buttons ); ?>
			</div>
		<?php
		
		if ( $listeo_verify_query->have_posts() ) { 
			$style_data = array(
				'style' 		=> $style, 
				'class' 		=> $custom_class, 
				'in_rows' 		=> $in_rows, 
				'grid_columns' 	=> $grid_columns,
				'per_page' 		=> $per_page,
				'max_num_pages'	=> $listeo_verify_query->max_num_pages, 
				'counter'		=> $listeo_verify_query->found_posts,
				'ajax_browsing' => $ajax_browsing,
				);
			$search_data = array_merge($style_data,$get_listings);
			$template_loader->set_template_data( $search_data )->get_template_part( 'listings-start' ); 
			
			// Loop through listings

			while ( $listeo_verify_query->have_posts() ) {
				// Setup listing data
				$listeo_verify_query->the_post();
				// $verify = get_post_meta(get_the_ID(),'_verified',true);
				
				$template_loader->set_template_data( $style_data )->get_template_part( 'content-listing',$style ); 	
			}
			// while ( $listeo_unverify_query->have_posts() ) {
			// 	// Setup listing data
			// 	$listeo_unverify_query->the_post();
			// 	// $verify = get_post_meta(get_the_ID(),'_verified',true);
				
			// 	$template_loader->set_template_data( $style_data )->get_template_part( 'content-listing',$style ); 	
			// }

						
			if($style_data['ajax_browsing'] == 'on'){?>
			</div>
			<div class="pagination-container margin-top-20 margin-bottom-20 ajax-search">
				<?php
				echo listeo_core_ajax_pagination( $listeo_verify_query->max_num_pages, 1 ); ?>
			</div>
			<?php } else {
				$template_loader->set_template_data( $style_data )->get_template_part( 'listings-end' ); 
			}
		} else {

			$template_loader->get_template_part( 'archive/no-found' ); 
		}

		wp_reset_query();
		return ob_get_clean();
	}

	
	
}


new Listeo_Core_Shortcodes();