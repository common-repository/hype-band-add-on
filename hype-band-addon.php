<?php
/*
Plugin Name: Hype Theme Band Add-on
Plugin URI: http://www.webdevstudios.com
Description: An add-on for the Hype Theme to add music, video, and tour functionality.
Version: 1.0.1
Author: WebDevStudios
Author URI: http://www.webdevstudios.com
License: GPLv2
*/

/**
 * Flush Rewrite Rules on Activation
 *
 * @since 1.0.1
 */
function hype_band_addon_activation() {
    hype_band_addon_register_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hype_band_addon_activation' );

/**
 * Register custom post types
 *
 * @since 1.0.0
 */
function hype_band_addon_register_cpt() {
    register_post_type( 'tour', array(
        'labels'                => array(
            'name'               => __( 'Tour Date', 'hype-band-add-on' ),
            'singular_name'      => __( 'Tour Date', 'hype-band-add-on' ),
            'add_new'            => __( 'Add New', 'hype-band-add-on' ),
            'add_new_item'       => __( 'Add New Tour Date', 'hype-band-add-on' ),
            'edit_item'          => __( 'Edit Tour Date', 'hype-band-add-on' ),
            'new_item'           => __( 'New Tour Date', 'hype-band-add-on' ),
            'view_item'          => __( 'View Tour Date', 'hype-band-add-on' ),
            'search_items'       => __( 'Search Tour Dates', 'hype-band-add-on' ),
            'not_found'          => __( 'No Tour Dates found', 'hype-band-add-on' ),
            'not_found_in_trash' => __( 'No Tour Dates found in Trash', 'hype-band-add-on' ),
            'parent_item_colon'  => __( '', 'hype-band-add-on' ),
            'menu_name'          => __( 'Tour Dates', 'hype-band-add-on' ),
        ),
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'tour' ),
        'capability_type'       => 'post',
        'hierarchical'          => false,
        'menu_position'         => null,
        'has_archive'           => true,
        'exclude_from_search'   => true,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        'taxonomies'            => array()
    ) );
}
add_action( 'init', 'hype_band_addon_register_cpt', 0 );

/**
 * Add some explanation text related to tours.
 *
 * @since 1.0.0
 */
function hype_band_addon_after_tour_title() {

    // If this isn't a tour post, bail here
    if ( 'tour' !== get_post_type() )
        return;

    // Show a notice about how to use this CPT
    printf( '<p>%s</p>', __( 'Note: the title field does not display on front-end. Use the fields below to include all relevant information about the tour.', 'hype-band-add-on' ) );

}
add_action( 'edit_form_after_title', 'hype_band_addon_after_tour_title' );

/**
 * Add custom columns to the post list screen
 *
 * @since  1.0.0
 * @param  array $defaults The default post columns
 * @return array           Our updated post columns
 */
function hype_band_addon_column_heading( $defaults ) {

    // Add our new columns
    $defaults['tour_date']    = __( 'Tour Date', 'hype-band-add-on' );
    $defaults['venue']        = __( 'Venue', 'hype-band-add-on' );
    $defaults['location']     = __( 'Location', 'hype-band-add-on' );
    $defaults['price']        = __( 'Price', 'hype-band-add-on' );
    $defaults['restrictions'] = __( 'Restrictions', 'hype-band-add-on' );

    return $defaults;

}
add_filter( 'manage_tour_posts_columns', 'hype_band_addon_column_heading' );

/**
 * Display the column content on the post list screen
 *
 * @since  1.0.0
 * @param  string $column_name The name of the current column
 * @return string              Custom column data
 */
function hype_band_addon_column_content( $column_name ) {
    switch ( $column_name ) {
        case 'tour_date':
            echo ( $tour_date = get_post_meta( get_the_ID(), 'tour_date', true ) )
                ? gmdate( 'F d, Y', intval( $tour_date ) )
                : __( 'No Date Specified', 'hype-band-add-on' );
            break;
        case 'venue' :
            echo ( $tour_venue_name = get_post_meta( get_the_ID(), 'tour_venue_name', true ) )
                ? esc_html( $tour_venue_name )
                : __( 'No Venue Specified', 'hype-band-add-on' );
            break;
        case 'location' :
            echo ( $tour_city = get_post_meta( get_the_ID(), 'tour_city', true ) )
                ? esc_html( $tour_city )
                : __( 'No Location Specified', 'hype-band-add-on' );
            break;
        case 'price' :
            echo ( $tour_price = get_post_meta( get_the_ID(), 'tour_price', true ) )
                ? '$' . absint( $tour_price )
                : __( 'No Price Specified', 'hype-band-add-on' );
            break;
        case 'restrictions' :
            echo ( $tour_restrictions = get_post_meta( get_the_ID(), 'tour_restrictions', true ) )
                ? esc_html( $tour_restrictions )
                : __( 'No Restrictions Specified', 'hype-band-add-on' );
            break;
        default:
            break;
    }
}
add_action( 'manage_tour_posts_custom_column' , 'hype_band_addon_column_content' );

/**
 * Hype Add-on Theme Settings
 *
 * @since 1.0.1
 */
function hype_band_addon_theme_settings() {
    if ( class_exists('sb_settings') ) {
        class hype_addon_settings extends sb_settings {
            function __construct() {
                $this->name     = __( 'Hype Band Settings', 'hype');
                $this->slug     = 'hype_addon_settings';
                $this->location = 'primary';
                $this->priority = 'high';
                $this->options  = array(
                    'soundcloud_playlist' => array(
                        'type'  => 'text',
                        'align' => 'right',
                        'size'  => 'large',
                        'label' => __( 'SoundCloud Playlist URL', 'hype' ),
                        'desc'  => __( 'Used to display a featured playlist in the content area.', 'hype-band-add-on' ),
                    ),
                    'bigcartel_account' => array(
                        'type'  => 'text',
                        'align' => 'right',
                        'size'  => 'large',
                        'label' => __( 'Big Cartel Store', 'hype' ),
                        'desc'  => __( 'Used to display a footer link.', 'hype-band-add-on' ),
                    )
                );
                parent::__construct();
            }

        }
        sb_register_settings( 'hype_addon_settings' );
    }
}
add_action( 'after_setup_theme', 'hype_band_addon_theme_settings' );
