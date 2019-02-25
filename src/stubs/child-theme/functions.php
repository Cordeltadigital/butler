<?php  
/*
Sonik Child theme
custom functions.php file
*/
add_action( 'wp_enqueue_scripts', 'qtc_theme_enqueue_styles' );
if(!function_exists('qtc_theme_enqueue_styles')) {
function qtc_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
    wp_enqueue_script( 'app.js', get_stylesheet_directory_uri(). '/js/app.js', ['wp-api'] );
}}

add_action( 'after_switch_theme', 'qtc_rewrite_flush_child' );
if(!function_exists('qtc_rewrite_flush_child')) {
function qtc_rewrite_flush_child() {
    flush_rewrite_rules();
}}

/* remove JetPack upsell messages */
add_filter( 'jetpack_just_in_time_msgs', '_return_false' );

add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );
