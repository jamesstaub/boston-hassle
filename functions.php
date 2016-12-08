<?php

/*

BOSTON HASSLE Theme Functions
Author: Kay Belardinelli
URL: http://kangabell.co

*/


/************* BASICS ***************/

// set maximum allowed width for content
if ( ! isset( $content_width ) )
    $content_width = 1140;

// remove WP version from RSS
add_filter('the_generator', 'bhass_rss_version');
// clean up gallery output in wp
add_filter('gallery_style', 'bhass_gallery_style');

// enqueue base scripts and styles
add_action('wp_enqueue_scripts', 'bhass_scripts_and_styles', 999);

// launching this stuff after theme setup
add_action('after_setup_theme','bhass_theme_support');
// adding sidebars to Wordpress (these are created in functions.php)
add_action( 'widgets_init', 'bhass_register_sidebars' );
// adding the search form
add_filter( 'get_search_form', 'bhass_wpsearch' );

// cleaning up random code around images & blockquotes
add_filter('the_content', 'bhass_filter_ptags_on_images');
add_filter('the_content', 'bhass_filter_ptags_on_blockquotes');
// cleaning up excerpt
add_filter('excerpt_more', 'bhass_excerpt_more');
// shorter excerpt
add_filter( 'excerpt_length', 'bhass_lengthen_excerpt', 999 );
// modify output of WordPress Popular Posts plugin
add_filter( 'wpp_custom_html', 'bhass_popular_posts_html', 10, 2 );



/*********************
CLEANUP
*********************/

// remove WP version from RSS
function bhass_rss_version() { return ''; }

// remove WP version from scripts
function bhass_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}

// remove injected CSS from gallery
function bhass_gallery_style($css) {
  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

// remove the p from around imgs & blockquotes (http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
function bhass_filter_ptags_on_images($content){
   return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}
function bhass_filter_ptags_on_blockquotes($content){
   return preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $content);
}

// Change "[...]more>>" to "...".
function bhass_excerpt_more($more) {
    global $post;
    return '...';
}

// Shorten excerpt length to 100 characters
function bhass_lengthen_excerpt( $length ) {
    return 82;
}

// Get name of first category in categories array
function category_name() {

    $category = get_the_category();

    //get the first category
    $name = $category[0]->cat_name;
    $cat_id = get_cat_ID( $name );
    $link = get_category_link( $cat_id );

    return '<p class="meta category">' . $name . '</p>';

}

function short_title() {

    $title = get_the_title();
    return mb_strimwidth($title, 0, 60, '...');

}


/*********************
SCRIPTS & ENQUEUEING
*********************/

// loading jquery and reply script
function bhass_scripts_and_styles() {
  if (!is_admin()) {

    // main stylesheet
    wp_register_style( 'bhass-stylesheet', get_stylesheet_directory_uri() . '/library/stylesheets/screen.css', array(), '', 'all' );

    // theme scripts file
    wp_register_script( 'bhass-js', get_stylesheet_directory_uri() . '/library/scripts/scripts.js', array( 'jquery' ), '', true );

    // enqueue styles and scripts
    wp_enqueue_style( 'bhass-stylesheet' );
    wp_enqueue_script( 'bhass-js' );

    // create site url variable to be used in js
    $translation_array = array( 'templateUrl' => get_stylesheet_directory_uri() );
    wp_localize_script( 'bhass-js', 'wpurl', $translation_array );

  }
}


/*********************
THEME SUPPORT
*********************/

// Adding WP 3+ Functions & Theme Support
function bhass_theme_support() {

    // rss thingy
    add_theme_support('automatic-feed-links');

    // wp menus
    add_theme_support( 'menus' );

    // registering wp3+ menus
    register_nav_menus(
        array(
            'main-nav' => __( 'The Main Menu', 'bhass' ),   // main nav in header
        )
    );

    // featured images
    add_theme_support( 'post-thumbnails' ); 

    add_image_size( 'grid-thumb', 600, 440, array( 'center', 'center') );
    
} /* end bhass theme support */


/*********************
MENUS & NAVIGATION
*********************/

// the main menu
function bhass_main_nav() {
    // display the wp3 menu if available
    wp_nav_menu(array(
        'container' => false,                           // remove nav container
        'container_class' => 'menu clearfix',           // class of container
        'menu' => __( 'The Main Menu', 'bhass' ),  // nav name
        'menu_class' => 'nav top-nav clearfix',         // adding custom nav class
        'theme_location' => 'main-nav',                 // where it's located in the theme
        'depth' => 2,                                   // limit the depth of the nav
        'fallback_cb' => 'bhass_main_nav_fallback'      // fallback function
    ));
} /* end bhass main nav */


/************* MODIFIED TITLE ********************/
// makes a nicely formatted title to go in the head of the document

function bhass_wp_title( $title, $sep ) {
    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo( 'name' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        $title = "$title $sep $site_description";

    return $title;
}
add_filter( 'wp_title', 'bhass_wp_title', 10, 2 );


/************* ACTIVE SIDEBARS ********************/

function bhass_register_sidebars() {
    register_sidebar(array(
        'id' => 'sidebar',
        'name' => __('Sidebar', 'bhass'),
        'description' => __('The sidebar for the archive/category pages.', 'bhass'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widgettitle">',
        'after_title' => '</h3>',
    ));
    register_sidebar(array(
        'id' => 'footer_links',
        'name' => __('Footer Links', 'bhass'),
        'description' => __('Links for the footer go here.', 'bhass'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '',
        'after_title' => '',
    ));
    register_sidebar(array(
        'id' => 'footer_address',
        'name' => __('Footer Address', 'bhass'),
        'description' => __('Company address for the footer.', 'bhass'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '',
        'after_title' => '',
    ));
    register_sidebar(array(
        'id' => 'footer_phone-email',
        'name' => __('Footer Contact Info', 'bhass'),
        'description' => __('Company phone number and email address for the footer.', 'bhass'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '',
        'after_title' => '',
    ));
}


/************* SEARCH FORM LAYOUT *****************/

// Search Form
function bhass_wpsearch($form) {
    $form = '<span class="icon-search"></span><form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
    <input type="search" value="' . get_search_query() . '" name="s" id="s" placeholder="Enter Text Here..." />
    <button type="submit"><span class="visually-hidden">Submit</span><span class="icon-arrow"></span></button>
    </form>';
    return $form;
}



?>