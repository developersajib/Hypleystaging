<?php
/*
Plugin Name: Purethemes.net CPT
Version: 1.3
Plugin URI: http://themeforest.net/user/purethemes/portfolio
Description: Plugin for Custom Post Types like Portfolio, Testimonials etc. used for Themes from <a href="http://purethemes.net">Purethemes.net</a>
Author: Purethemes.net
Author URI: http://themeforest.net/user/purethemes/portfolio
*/



/* ----------------------------------------------------- */
/* Portfolio Custom Post Type */
/* ----------------------------------------------------- */
    
function pt_register_portfolio(){
    add_action( 'init', 'register_cpt_portfolio' );
    function register_cpt_portfolio() {

        $labels = array(
            'name' => __( 'Portfolio','purepress'),
            'singular_name' => __( 'Portfolio','purepress'),
            'add_new' => __( 'Add New','purepress' ),
            'add_new_item' => __( 'Add New Work','purepress' ),
            'edit_item' => __( 'Edit Work','purepress'),
            'new_item' => __( 'New Work','purepress'),
            'view_item' => __( 'View Work','purepress'),
            'search_items' => __( 'Search Portfolio','purepress'),
            'not_found' => __( 'No portfolio found','purepress'),
            'not_found_in_trash' => __( 'No works found in Trash','purepress'),
            'parent_item_colon' => __( 'Parent work:','purepress'),
            'menu_name' => __( 'Portfolio','purepress'),
            );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => __('Display your works by filters','purepress'),
            'supports' => array( 'title', 'editor', 'excerpt', 'revisions', 'thumbnail' ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => array( 'slug' => apply_filters('purethemes_custom_slug_portfolio','portfolio')),
            'capability_type' => 'post'
            );

        register_post_type( 'portfolio', $args );
    }
    

    /* ----------------------------------------------------- */
    /* Filter Taxonomy */
    /* ----------------------------------------------------- */
    if (!function_exists('register_taxonomy_filters')) {
        add_action( 'init', 'register_taxonomy_filters' );

        function register_taxonomy_filters() {

            $labels = array(
                'name' => __( 'Filters', 'purepress' ),
                'singular_name' => __( 'Filter', 'purepress' ),
                'search_items' => __( 'Search Filters', 'purepress' ),
                'popular_items' => __( 'Popular Filters', 'purepress' ),
                'all_items' => __( 'All Filters', 'purepress' ),
                'parent_item' => __( 'Parent Filter', 'purepress' ),
                'parent_item_colon' => __( 'Parent Filter:', 'purepress' ),
                'edit_item' => __( 'Edit Filter', 'purepress' ),
                'update_item' => __( 'Update Filter', 'purepress' ),
                'add_new_item' => __( 'Add New Filter', 'purepress' ),
                'new_item_name' => __( 'New Filter', 'purepress' ),
                'separate_items_with_commas' => __( 'Separate Filters with commas', 'purepress' ),
                'add_or_remove_items' => __( 'Add or remove Filters', 'purepress' ),
                'choose_from_most_used' => __( 'Choose from the most used Filters', 'purepress' ),
                'menu_name' => __( 'Filters', 'purepress' ),
                );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true,
                'rewrite' => array( 'slug' => apply_filters('purethemes_custom_slug_filters','filters')),
                'query_var' => true
                );
            register_taxonomy( 'filters', array('portfolio'), $args );
        }

        if (!function_exists('astrum_custom_taxonomy_post_class')) {
        /*
         * Adds terms from a custom taxonomy to post_class
         */
        add_filter( 'post_class', 'purethemes_filters_post_class', 10, 3 );

            function purethemes_filters_post_class( $classes, $class, $ID ) {
                $taxonomy = 'filters';
                $terms = get_the_terms( (int) $ID, $taxonomy );
                if( !empty( $terms ) ) {
                    foreach( (array) $terms as $order => $term ) {
                        if( !in_array( $term->slug, $classes ) ) {
                            $classes[] = $term->slug;
                        }
                    }
                }
                return $classes;
            }
        }
    }
}
add_action('purethemes-portfolio','pt_register_portfolio');



/* ----------------------------------------------------- */
/* Testimonials Custom Post Type */
/* ----------------------------------------------------- */

    
function pt_register_testimonials(){
    
    function register_cpt_testimonials() {

        $labels = array(
            'name' => __( 'Testimonials','purepress'),
            'singular_name' => __( 'testimonial','purepress'),
            'add_new' => __( 'Add New','purepress' ),
            'add_new_item' => __( 'Add New Testimonial','purepress' ),
            'edit_item' => __( 'Edit Testimonial','purepress'),
            'new_item' => __( 'New Testimonial','purepress'),
            'view_item' => __( 'View Testimonial','purepress'),
            'search_items' => __( 'Search Testimonials','purepress'),
            'not_found' => __( 'No testimonials found','purepress'),
            'not_found_in_trash' => __( 'No testimonials found in Trash','purepress'),
            'parent_item_colon' => __( 'Parent testimonial:','purepress'),
            'menu_name' => __( 'Testimonials','purepress'),
            );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => __('Display your works by filters','purepress'),
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => array( 'slug' => apply_filters('purethemes_custom_slug_testimonial','testimonial')),
            'capability_type' => 'post'
            );

        register_post_type( 'testimonial', $args );
    }
    add_action( 'init', 'register_cpt_testimonials' );
}
add_action('purethemes-testimonials','pt_register_testimonials');


/* ----------------------------------------------------- */
/* Team Custom Post Type */
/* ----------------------------------------------------- */
function pt_register_team(){
    
    function register_cpt_team() {

        $labels = array(
            'name' => __( 'Team','purepress'),
            'singular_name' => __( 'Team','purepress'),
            'add_new' => __( 'Add New','purepress' ),
            'add_new_item' => __( 'Add New Team Member','purepress' ),
            'edit_item' => __( 'Edit Team Member','purepress'),
            'new_item' => __( 'New Team Member','purepress'),
            'view_item' => __( 'View Team Member','purepress'),
            'search_items' => __( 'Search Team Members','purepress'),
            'not_found' => __( 'No Team Members found','purepress'),
            'not_found_in_trash' => __( 'No Team Members found in Trash','purepress'),
            'parent_item_colon' => __( 'Parent member:','purepress'),
            'menu_name' => __( 'Team','purepress'),
            );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => array( 'slug' => apply_filters('purethemes_custom_slug_team','team')),
            'capability_type' => 'post'
            );
        register_post_type( 'team', $args );
    }
    add_action( 'init', 'register_cpt_team' );
}
add_action('purethemes-team','pt_register_team');


/* ----------------------------------------------------- */
/* Portfolio Custom Post Type */
/* ----------------------------------------------------- */
    
function pt_register_projects(){
    add_action( 'init', 'register_cpt_project' );
        function register_cpt_project() {
            $labels = array(
                'name'                  => _x( 'Projects', 'Post Type General Name', 'purepress' ),
                'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'purepress' ),
                'menu_name'             => __( 'Projects', 'purepress' ),
                'name_admin_bar'        => __( 'Projects', 'purepress' ),
                'archives'              => __( 'Projects Archives', 'purepress' ),
                'parent_item_colon'     => __( 'Parent Item:', 'purepress' ),
                'all_items'             => __( 'All Projects', 'purepress' ),
                'add_new_item'          => __( 'Add New Project', 'purepress' ),
                'add_new'               => __( 'Add New', 'purepress' ),
                'new_item'              => __( 'New Item', 'purepress' ),
                'edit_item'             => __( 'Edit Item', 'purepress' ),
                'update_item'           => __( 'Update Item', 'purepress' ),
                'view_item'             => __( 'View Item', 'purepress' ),
                'search_items'          => __( 'Search Item', 'purepress' ),
                'not_found'             => __( 'Not found', 'purepress' ),
                'not_found_in_trash'    => __( 'Not found in Trash', 'purepress' ),
                'featured_image'        => __( 'Featured Image', 'purepress' ),
                'set_featured_image'    => __( 'Set featured image', 'purepress' ),
                'remove_featured_image' => __( 'Remove featured image', 'purepress' ),
                'use_featured_image'    => __( 'Use as featured image', 'purepress' ),
                'insert_into_item'      => __( 'Insert into item', 'purepress' ),
                'uploaded_to_this_item' => __( 'Uploaded to this item', 'purepress' ),
                'items_list'            => __( 'Items list', 'purepress' ),
                'items_list_navigation' => __( 'Items list navigation', 'purepress' ),
                'filter_items_list'     => __( 'Filter items list', 'purepress' ),
            );
            $args = array(
                'label'                 => __( 'Project', 'purepress' ),
                'description'           => __( 'Projects', 'purepress' ),
                'labels'                => $labels,
                'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', ),
                'rewrite'               => array( 'slug' => apply_filters('purethemes_custom_slug_projects','projects')),
                'hierarchical'          => false,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'menu_icon'             => 'dashicons-admin-home',
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => true,        
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'capability_type'       => 'page',
            );
            register_post_type( 'projects', $args );
        }

    /* ----------------------------------------------------- */
    /* Project Category Taxonomy */
    /* ----------------------------------------------------- */
    if (!function_exists('register_taxonomy_proj_cats')) {
        add_action( 'init', 'register_taxonomy_proj_cats' );

        function register_taxonomy_proj_cats() {

            $labels = array(
                'name' => __( 'Project Categories', 'purepress' ),
                'singular_name' => __( 'Project Category', 'purepress' ),
                'search_items' => __( 'Search Project Categories', 'purepress' ),
                'popular_items' => __( 'Popular Project Categories', 'purepress' ),
                'all_items' => __( 'All Project Categories', 'purepress' ),
                'parent_item' => __( 'Parent Category', 'purepress' ),
                'parent_item_colon' => __( 'Parent Category:', 'purepress' ),
                'edit_item' => __( 'Edit Category', 'purepress' ),
                'update_item' => __( 'Update Category', 'purepress' ),
                'add_new_item' => __( 'Add New Category', 'purepress' ),
                'new_item_name' => __( 'New Category', 'purepress' ),
                'separate_items_with_commas' => __( 'Separate Category with commas', 'purepress' ),
                'add_or_remove_items' => __( 'Add or remove Category', 'purepress' ),
                'choose_from_most_used' => __( 'Choose from the most used Category', 'purepress' ),
                'menu_name' => __( 'Project Categories', 'purepress' ),
                );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => true,
                'show_admin_column' => true,
                'hierarchical' => true,
                'rewrite'  => array( 'slug' => apply_filters('purethemes_custom_slug_project_categories','project_categories')),
                'query_var' => true
                );
            register_taxonomy( 'project_categories', array('projects'), $args );
        }

        if (!function_exists('purethemes_project_categories_post_class')) {
            /*
             * Adds terms from a custom taxonomy to post_class
             */
            add_filter( 'post_class', 'purethemes_project_categories_post_class', 10, 3 );

            function purethemes_project_categories_post_class( $classes, $class, $ID ) {
                $taxonomy = 'project_categories';
                $terms = get_the_terms( (int) $ID, $taxonomy );
                if( !empty( $terms ) ) {
                    foreach( (array) $terms as $order => $term ) {
                        if( !in_array( $term->slug, $classes ) ) {
                            $classes[] = $term->slug;
                        }
                    }
                }
                return $classes;
            }
        }
    }
}
add_action('purethemes-project','pt_register_projects');


/* ----------------------------------------------------- */
/* Portfolio Custom Post Type */
/* ----------------------------------------------------- */
    
function pt_register_services(){
    add_action( 'init', 'register_cpt_services' );
        function register_cpt_services() {
            $labels = array(
                'name'                  => _x( 'Services', 'Post Type General Name', 'purepress' ),
                'singular_name'         => _x( 'Service', 'Post Type Singular Name', 'purepress' ),
                'menu_name'             => __( 'Services', 'purepress' ),
                'name_admin_bar'        => __( 'Services', 'purepress' ),
                'archives'              => __( 'Services Archives', 'purepress' ),
                'parent_item_colon'     => __( 'Parent Item:', 'purepress' ),
                'all_items'             => __( 'All Services', 'purepress' ),
                'add_new_item'          => __( 'Add New Service', 'purepress' ),
                'add_new'               => __( 'Add New', 'purepress' ),
                'new_item'              => __( 'New Item', 'purepress' ),
                'edit_item'             => __( 'Edit Item', 'purepress' ),
                'update_item'           => __( 'Update Item', 'purepress' ),
                'view_item'             => __( 'View Item', 'purepress' ),
                'search_items'          => __( 'Search Item', 'purepress' ),
                'not_found'             => __( 'Not found', 'purepress' ),
                'not_found_in_trash'    => __( 'Not found in Trash', 'purepress' ),
                'featured_image'        => __( 'Featured Image', 'purepress' ),
                'set_featured_image'    => __( 'Set featured image', 'purepress' ),
                'remove_featured_image' => __( 'Remove featured image', 'purepress' ),
                'use_featured_image'    => __( 'Use as featured image', 'purepress' ),
                'insert_into_item'      => __( 'Insert into item', 'purepress' ),
                'uploaded_to_this_item' => __( 'Uploaded to this item', 'purepress' ),
                'items_list'            => __( 'Items list', 'purepress' ),
                'items_list_navigation' => __( 'Items list navigation', 'purepress' ),
                'filter_items_list'     => __( 'Filter items list', 'purepress' ),
            );
            $args = array(
                'label'                 => __( 'Service', 'purepress' ),
                'description'           => __( 'Services', 'purepress' ),
                'labels'                => $labels,
                'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', ),
                'rewrite'  => array( 'slug' => apply_filters('purethemes_custom_slug_service','service')),
                'hierarchical'          => false,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'menu_icon'             => 'dashicons-admin-home',
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => true,        
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'capability_type'       => 'page',
            );
            register_post_type( 'service', $args );
        }

}
add_action('purethemes-services','pt_register_services');


?>