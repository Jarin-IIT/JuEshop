<?php
/*
Plugin Name:  Bangla Fonts Collection
Plugin URI:   http://robertbiswas.com/bangla-fonts-collection-a-wordpress-plugin/
Description:  Activate some great Bangla fonts in your WordPress website
Version:      2.2
Author:       Robert Biswas
Author URI:   http://robertbiswas.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  banglafontscol
Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
* Loading textdomain
*/
function banglafontscol_load_textdomain() {
    load_plugin_textdomain('banglafontscol', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'banglafontscol_load_textdomain' );

/*
* Adding Assets
*/
function banglafontscol_add_fonts(){
    wp_enqueue_style( "bangla-fonts-css",  plugins_url( 'assets/bangla-fonts.css', __FILE__ ), null, '1.0' );
    wp_enqueue_style( "bangla-fonts-custom-css",  plugins_url( 'assets/bangla-fonts-custom.css', __FILE__ ), null, '1.0' );
}
add_action('wp_enqueue_scripts', 'banglafontscol_add_fonts');

/*
* Adding Admin Assets
*/
function banglafontscol_admin_assets($screen){
    $varr = time();
    wp_enqueue_script('avro-js', plugin_dir_url(__FILE__). '/assets/js/avro-v1.1.4.min.js', array('jquery'), '1.1.4', true);
    wp_enqueue_script('banglafontscol-admin-js', plugin_dir_url(__FILE__). '/assets/js/bfc-admin-script.js', array('avro-js'), $varr, true);
    wp_enqueue_style('bfc-admin-style', plugin_dir_url(__FILE__). '/assets/css/bfc-admin-style.css', false, $varr);
}
add_action('admin_enqueue_scripts', 'banglafontscol_admin_assets');
/*
* Adding Setting Page
*/
require_once plugin_dir_path(__FILE__)."includes/settings.php";
// Setting link creation for setting page
function banglafontscol_settings_link($links){
    $newlink = sprintf("<a href= '%s'>%s</a>", 'admin.php?page=banglafonts', __('Settings', 'banglafontscol'));
    $links[] = $newlink;
    return $links;
}
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'banglafontscol_settings_link'); 

/*
* Adding Custom CSS of user to the head
*/
function banglafontscol_fontface_css(){
    $bfc_style = get_option('banglafontselectors');
    wp_add_inline_style('bangla-fonts-custom-css', $bfc_style );
}
add_action('wp_enqueue_scripts', 'banglafontscol_fontface_css');

/*
* Adding Button to Admin Ban
*/
function banglafontscol_admin_button($wp_admin_bar){
$args = array(
    'id' => 'bfc-language-changer',
    'title' => 'English Enabled',
    'href' => '#',
    'meta' => array(
        'class' => 'bfc-changer-button',
    )
);
$wp_admin_bar->add_node($args);
}

add_action('admin_bar_menu', 'banglafontscol_admin_button', 999);