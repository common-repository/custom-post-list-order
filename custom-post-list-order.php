<?php
/*
  Plugin Name: Custom Post List Order
  Plugin URI: https://blog-bootcamp.jp/custom-post-list-order/
  Description: Sort the posted articles in order of published date or update date, page view.
  Version: 1.0.2
  Author: BLOG BOOT CAMP
  Author URI: https://blog-bootcamp.jp
  License: GPLv2 or later
 */

/*  Copyright 2021 BLOG BOOT CAMP

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Define
define( 'CPLO_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPLO_NAME', 'custom-post-list-order' );


// Install hook
function cplo_install() {
    $settings = get_option( CPLO_NAME );

    if ( ! $settings ) {
        $input_settings = array(
            'targets'  => array('Top Page', 'Category Pages', 'Tag Pages'), 
            'order_by' => 'Published Date', 
            'order'    => 'DESC'
        );
        update_option( CPLO_NAME, $input_settings );
    } 

}
register_activation_hook( __FILE__, 'cplo_install' );


// Uninstall hook
function cplo_uninstall() {
    delete_option( CPLO_NAME );
}
register_deactivation_hook( __FILE__, 'cplo_uninstall' );


// Class
$cplo = new CustomPostListOrder();
class CustomPostListOrder
{
    const PLUGIN_ID         = CPLO_NAME;
    const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
    const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';

    // Construct
    function __construct(){
        if ( is_admin() ) {
            // Add menu
            add_action( 'admin_menu', array($this, 'add_plugin_menu') );
            add_action( 'admin_init', array($this, 'save_setting') );
        }
    }
    // Method
    function add_plugin_menu() {
        add_options_page(
             'Custom Post List Order - Settings', // Page Title
             'Custom Post List Order', // Menu Title
             'administrator', // Capability
             self::PLUGIN_ID, //Menu Slug
             array( $this, 'show_setting_page' ) // Function
        );
   }

   function show_setting_page() {
       require CPLO_DIR.'includes/setting.php';
   }

   function save_setting() {
       $target_white_list = array( 'Top Page', 'Category Pages', 'Tag Pages', '' );
       $orderby_white_list = array('Published Date', 'Modified Date', 'Page Views', 'Random', '' );
       $order_white_list = array('DESC', 'ASC', '' );

       if ( isset( $_POST[self::CREDENTIAL_NAME]) && $_POST[self::CREDENTIAL_NAME] ) {
           if ( check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ) {
               $input_settings = array();
               $input_settings['targets'] = isset( $_POST['target'] ) ? array_map( 'sanitize_text_field', $_POST['target'] ) : '';
               $input_settings['order_by'] = isset( $_POST['order_by'] ) ? sanitize_text_field( $_POST['order_by'] ) : '';
               $input_settings['order'] = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : '';

               // Validation
               if ($input_settings['targets']) {
                   foreach($input_settings['targets'] as $target) {
                       if ( in_array( $target, $target_white_list ) ) {
                           $target = $target;
                        } else {
                            $target = '';
                        }
                    }
                }
               if ( in_array( $input_settings['order_by'], $orderby_white_list ) ) {
                   $input_settings['order_by'] = $input_settings['order_by'];
               } else {
                   $input_settings['order_by'] = '';
               }
               if ( in_array( $input_settings['order'], $order_white_list ) ) {
                   $input_settings['order'] = $input_settings['order'];
               } else {
                   $input_settings['order'] = '';
               }
               
               update_option( self::PLUGIN_ID, $input_settings );
            }
        }
   }
}


// Re-Order post list
$settings = get_option( CPLO_NAME );

$target = array();
if ( $settings['targets'] && in_array('Top Page', $settings['targets']) ) {
    $target[] = 'is_home';
}
if ( $settings['targets'] && in_array('Category Pages', $settings['targets']) ) {
    $target[] = 'is_category';
}
if ( $settings['targets'] && in_array('Tag Pages', $settings['targets']) ) {
    $target[] = 'is_tag';
}

if ( $settings['order_by'] == 'Published Date' ) {
    $orderby = 'date';
} elseif ( $settings['order_by'] == 'Modified Date' ) {
    $orderby = 'modified';
} elseif ( $settings['order_by'] == 'Page Views' ) {
    $orderby = 'meta_value_num';
} elseif ( $settings['order_by'] == 'Random' ) {
    $orderby = 'rand';
}

if ( $settings['order'] == 'DESC' ) {
    $order = 'DESC';
} elseif ( $settings['order'] == 'ASC' ) {
    $order = 'ASC';
}

if ( $target ) {
    $funcNameList = ["is_home", "is_tag"];
    add_action( 'pre_get_posts', function( $query ) use( $target, $orderby, $order ) { 
        if ( in_array( 'is_home', $target ) ) {
            if ( $query->is_home() ) {
                $query->set( 'orderby', $orderby );
                $query->set( 'order', $order );
                if ( $orderby == 'meta_value_num' ){
                    $query->set( "meta_key", "page_views_cplo" );
                }
            }
        }
        if ( in_array( 'is_category', $target ) ) {
            if ( $query->is_category() ) {
                $query->set( 'orderby', $orderby );
                $query->set( 'order', $order );
                if ( $orderby == 'meta_value_num' ){
                    $query->set( "meta_key", "page_views_cplo" );
                }
            }
        }
        if ( in_array( 'is_tag', $target ) ) {
            if ( $query->is_tag() ) {
                $query->set( 'orderby', $orderby );
                $query->set( 'order', $order );
                if ( $orderby == 'meta_value_num' ){
                    $query->set( "meta_key", "page_views_cplo" );
                }
            }
        }
     } );
}


// Link to Settings Page
function cplo_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=custom-post-list-order') ) .'">Settings</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'cplo_action_links' );


// Count Page Views
function cplo_count_page_views() {
    $post_id = get_the_ID();
    $custom_key = 'page_views_cplo';
    $view_count = get_post_meta( $post_id, $custom_key, true ); 

    if ( is_single() && !(is_user_logged_in())) {
        if ( $view_count === '' ) {
            delete_post_meta( $post_id, $custom_key );
            add_post_meta( $post_id, $custom_key, '0' );
        } else {
            $view_count++;
            update_post_meta( $post_id, $custom_key, $view_count );
        }
    }
}
add_action( 'wp_head',  'cplo_count_page_views' );
?>