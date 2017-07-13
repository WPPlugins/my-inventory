<?php
/**
 * Plugin Name:       My Inventory
 * Plugin URI:        elinemm.com/wp/myi-inventory
 * Description:       A plugin that adds basic inventory management capabilities to Wordpress. Only tested for iribbon and ocin-lite themes. Set myMenu to primary/main menu if see more than users and logout in menu. Bootstrap shall be using version 3. (Set in Twitter Bootstrap plugin). Shall see Users and Logout menu and Users shall have submenu. Otherwise will not work for that template. Also test the user->edit dropdown is working and the report/any log is working. Awada, Nomad theme seems working but not tested thoroughly. Do not ever change the role of the administrator using the application else it might change his wordpress role to subscriber and you will have no way to change back (except via database). Administrator shall only be used to set up the first power user. This plugin will not be actively maintained. Thanks the plugin assessors for all the sound advices they had given me and also for their hardwork and full dedication.
 * Version:           1.0.7
 * Author:            Ng Kock Leong
 * Author URi:        https://plus.google.com/u/0/103834511059437608696
 * License:           GPL-2.0+ for this plug-in, MIT License for bootstrap, bootstrap datatable, bootstrap select, JQuery Validation
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html, https://opensource.org/licenses/mit-license.php
 * Text Domain:       myi-inventory
 * Plugin Dependencies WordPress Twitter Bootstrap (from iControlWP), Insert PHP
 *
 * Bootstrap shall be using version 3.
 * PHP must be >= 5.3.0 as using namespace.
 * if all the other menus items appear (except for login and logout), probably menu location is not 'Primary'
 *   See function myi_add_menus function to see how to change it in your theme
 *
 * Below is a description of the functionality allowed for each user roles rights
 *   view_inventory - can see/select from the inventory,
 *   stock_mod_inventory - can add/remove stocks from inventory
 *   create_inventory - can create new inventory (this is only for txt table)
 *   mod_inventory - can modify current inventory (this is only for txt table),
 *   delete_inventory - can delete inventory (this is only for txt table),
 *   view_inventory_master - can see/select from any master tables (starts with @wp_@_myi_mst. note that @wp_@ is the wp table prefix)
 *   mod_inventory_master - can modify any of the mst tables,
 *   create_inventory_master - can create any of the mst tables,
 *   delete_inventory_master - can delete any of the mst tables,
 *   create_user - can create a new user
 *   mod_user - can modify the new user
 *   delete_user - can delete the user. Note that user is not physically deleted in wp_users tables. To physically delete, use wordpress dashboard users. 
 *                 please take note that if physically deleting the user might break any past logs/transaction
 *   mod_roles - can modify the roles for any users. Do not modify the administrator as it will change the administrator role.
 *   view_logs - can view the logs
 *   view_reports - can view the reports
 *
 *   when enqueue css for child theme, remember to set priority to more than 99999 so that it will load after the plugin
 *
 *   when using ocin-lite themes, remember to set the background color of submenu via css to non-white so that can see the submenu wordings
 */
namespace my_inventory;
 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// note that I choose require_once instead of include_once as when hit error (eg function already defined, wants to stops execution else
// might give unexpected results (break the plugin)
require_once( plugin_dir_path( __FILE__ ) . '/inc/main/myi_main_menu_functions.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/main/myi_main_other_functions.php');
    
if ( ! class_exists( '\\my_inventory\\Myi_Inventory_Plugin' ) ) { 
    require_once( plugin_dir_path( __FILE__ ) . '/inc/myi_user_client_roles.php');
    
    class Myi_Inventory_Plugin {      
        /**
         * Initializes the plugin.
         *
         * To keep the initialization fast, only add filter and action
         * hooks in the constructor.
         */
        public function __construct() {
        }   // Constructor

        /**
         * Plugin activation hook.
         *
         * Creates all WordPress pages needed by the plugin.
         */
        public static function plugin_activated() {
            // check dependecies
            Myi_Inventory_Plugin::check_dependencies();
            
            // remove all roles
            Myi_Inventory_Plugin::remove_all_roles();
            
            // add new roles (no longer used)
            Myi_Inventory_Plugin::add_the_roles();
            
            // add in the tables into the database if does not exist.
            Myi_Inventory_Plugin::run_sql_script( '/assets/setup_tables.sql' );       

            // add in the pages
            Myi_Inventory_Plugin::add_pages();

        } // plugin activated         
        
        /**
         * Check plugins dependecies
         *
         */
        public static function check_dependencies() {
            // ensure dependent plugins are installed
            // WordPress Twitter Bootstrap (from iControlWP)
            if ( !in_array( 'wordpress-bootstrap-css/hlt-bootstrapcss.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
                // Deactivate the plugin
				deactivate_plugins(__FILE__);
				
				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires <a href="https://wordpress.org/plugins/wordpress-bootstrap-css/">Bootstrap CSS :: WordPress Twitter Bootstrap (from iControlWP)</a> plugins to be active!', 'my-inventory');
				die($error_message);                
            }    

            // Insert PHP 
            if ( !in_array( 'insert-php/insert_php.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { 
                // Deactivate the plugin
				deactivate_plugins(__FILE__);
				
				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires <a href="https://wordpress.org/plugins/insert-php/">Insert PHP</a> plugins to be active!', 'my-inventory');
				die($error_message);                
            }             
        }
        
        /**
         * Add in all the pages
         *
         */        
        public static function add_pages() {
            // Information needed for creating the plugin's pages
            $plugin_path = plugin_dir_path( __FILE__ );
            
            $page_definitions = array(
                'myi-view-client' => array(
                    'title' => __( 'View Client', 'myi-view-client' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-client.php\');[/insert_php]',
                ),
                
                'myi-add-client' => array(
                    'title' => __( 'Add New Client', 'myi-add-client' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-client.php\');[/insert_php]',
                ),
                
                'myi-mod-client' => array(
                    'title' => __( 'Edit Client', 'myi-mod-client' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-client.php\');[/insert_php]',
                ),
                
                'myi-del-client' => array(
                    'title' => __( 'Delete Client', 'myi-del-client' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-client.php\');[/insert_php]',
                ),

                'myi-view-prod' => array(
                    'title' => __( 'View Product', 'myi-view-prod' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-prod.php\');[/insert_php]',
                ),                
                
                'myi-add-prod' => array(
                    'title' => __( 'Add New Product', 'myi-add-prod' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-prod.php\');[/insert_php]',
                ),                
                
                'myi-mod-prod' => array(
                    'title' => __( 'Edit Product', 'myi-mod-prod' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-prod.php\');[/insert_php]',
                ),                
                
                'myi-del-prod' => array(
                    'title' => __( 'Delete Product', 'myi-del-prod' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-prod.php\');[/insert_php]',
                ),      

                'myi-view-uom' => array(
                    'title' => __( 'View UOM', 'myi-view-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-uom.php\');[/insert_php]',
                ),                
                
                'myi-add-uom' => array(
                    'title' => __( 'Add New UOM', 'myi-add-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-uom.php\');[/insert_php]',
                ),                
                
                'myi-mod-uom' => array(
                    'title' => __( 'Edit UOM', 'myi-mod-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-uom.php\');[/insert_php]',
                ),                
                
                'myi-del-uom' => array(
                    'title' => __( 'Delete UOM', 'myi-del-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-uom.php\');[/insert_php]',
                ),     

                'myi-view-cat' => array(
                    'title' => __( 'View Category', 'myi-view-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-cat.php\');[/insert_php]',
                ),                
                
                'myi-add-cat' => array(
                    'title' => __( 'Add New Category', 'myi-add-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-cat.php\');[/insert_php]',
                ),                
                
                'myi-mod-cat' => array(
                    'title' => __( 'Edit Category', 'myi-mod-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-cat.php\');[/insert_php]',
                ),                
                
                'myi-del-cat' => array(
                    'title' => __( 'Delete Category', 'myi-del-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-cat.php\');[/insert_php]',
                ),

                'myi-view-loc' => array(
                    'title' => __( 'View Location', 'myi-view-loc' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-loc.php\');[/insert_php]',
                ),
                
                'myi-add-loc' => array(
                    'title' => __( 'Add New Location', 'myi-add-loc' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-loc.php\');[/insert_php]',
                ),
                
                'myi-mod-loc' => array(
                    'title' => __( 'Edit Location', 'myi-mod-loc' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-loc.php\');[/insert_php]',
                ),
                
                'myi-del-loc' => array(
                    'title' => __( 'Delete Location', 'myi-del-loc' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-loc.php\');[/insert_php]',
                ),
                
                'myi-view-cat-link' => array(
                    'title' => __( 'View Product/Category links', 'myi-view-cat-link' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-cat-link.php\');[/insert_php]',
                ),  

                'myi-link-cat' => array(
                    'title' => __( 'Link Product to Category', 'myi-link-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-link-cat.php\');[/insert_php]',
                ),                
                
                'myi-unlink-cat' => array(
                    'title' => __( 'Unlink Product from Category', 'myi-unlink-cat' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-unlink-cat.php\');[/insert_php]',
                ),                
                
                'myi-view-prod-uom' => array(
                    'title' => __( 'View UOMs assigned to Product', 'myi-view-prod-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-prod-uom.php\');[/insert_php]',
                ),

                'myi-add-prod-uom' => array(
                    'title' => __( 'Set UOMs for Product', 'myi-add-prod-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-prod-uom.php\');[/insert_php]',
                ),

                'myi-copy-prod-uom' => array(
                    'title' => __( 'Copy UOMs to another client', 'myi-copy-prod-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-copy-prod-uom.php\');[/insert_php]',
                ),

                'myi-del-prod-uom' => array(
                    'title' => __( 'Remove UOMs from Product', 'myi-del-prod-uom' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-prod-uom.php\');[/insert_php]',
                ),

                'myi-view-stock' => array(
                    'title' => __( 'View Stocks', 'myi-view-stock' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-view-stock.php\');[/insert_php]',
                ),  
                
                'myi-stock-in' => array(
                    'title' => __( 'Stock In/Out', 'myi-stock-in' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-stock-in.php\');[/insert_php]',
                ),  

                'myi-stock-trans' => array(
                    'title' => __( 'Transfer Stocks', 'myi-stock-trans' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-stock-trans.php\');[/insert_php]',
                ),

                'myi-add-user' => array(
                    'title' => __( 'Add New User', 'myi-add-user' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-add-user.php\');[/insert_php]',
                ),

                'myi-mod-user' => array(
                    'title' => __( 'Edit User', 'myi-mod-user' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-mod-user.php\');[/insert_php]',
                ),

                'myi-del-user' => array(
                    'title' => __( 'Delete User', 'myi-del-user' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-del-user.php\');[/insert_php]',
                ),

                'myi-assign-roles' => array(
                    'title' => __( 'Assign Roles for clients to user', 'myi-assign-roles' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-assign-roles.php\');[/insert_php]',
                ),

                'myi-unassign-roles' => array(
                    'title' => __( 'Unassign Roles for clients to user', 'myi-unassign-roles' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-unassign-roles.php\');[/insert_php]',
                ),

                'myi-copy-roles' => array(
                    'title' => __( 'Copy Roles from 1 user to another user', 'myi-copy-roles' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-copy-roles.php\');[/insert_php]',
                ),

                'myi-inv-log' => array(
                    'title' => __( 'Inventory Logs', 'myi-inv-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-inv-log.php\');[/insert_php]',
                ),

                'myi-cat-log' => array(
                    'title' => __( 'Category Logs', 'myi-cat-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-cat-log.php\');[/insert_php]',
                ),

                'myi-cat-prod-log' => array(
                    'title' => __( 'Category/Product Linkage Logs', 'myi-cat-prod-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-cat-prod-log.php\');[/insert_php]',
                ),
                
                'myi-client-log' => array(
                    'title' => __( 'Client Logs', 'myi-client-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-client-log.php\');[/insert_php]',
                ),

                'myi-loc-log' => array(
                    'title' => __( 'Location Logs', 'myi-loc-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-loc-log.php\');[/insert_php]',
                ),

                'myi-prod-log' => array(
                    'title' => __( 'Product Logs', 'myi-prod-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-prod-log.php\');[/insert_php]',
                ),

                'myi-uom-log' => array(
                    'title' => __( 'UOM Logs', 'myi-uom-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-uom-log.php\');[/insert_php]',
                ),

                'myi-prod-uom-log' => array(
                    'title' => __( 'Product UOM Setup Logs', 'myi-prod-uom-log' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-prod-uom-log.php\');[/insert_php]',
                ),

                'myi-inv-rpt' => array(
                    'title' => __( 'Stocks Count Report', 'myi-inv-rpt' ),
                    'content' => '[insert_php]require_once( \'' .$plugin_path .'/inc/GUI/myi-inv-rpt.php\');[/insert_php]',
                ),

            );
     
            foreach ( $page_definitions as $slug => $page ) {
                // overwrite the pages if already exists (based on slug)
                $query = new \WP_Query( 'pagename=' . $slug );
                if ( $query->have_posts() ) { 
                    wp_delete_post( $query->get_queried_object_id(), true );
                }
                
                // Add the page using the data from the array above
                wp_insert_post(
                    array(
                            'post_content'   => $page['content'],
                            'post_name'      => $slug,
                            'post_title'     => $page['title'],
                            'post_status'    => 'publish',
                            'post_type'      => 'page',
                            'ping_status'    => 'closed',
                            'comment_status' => 'closed',
                    )
                );
            }

            // create member-account page if not already exists
                // Check that the page doesn't exist already
                $query = new \WP_Query( 'pagename=member-account');
                if ( ! $query->have_posts() ) {
                    // Add the page using the data from the array above
                    wp_insert_post(
                        array(
                            'post_content'   => 'Welcome',
                            'post_name'      => 'member-account',
                            'post_title'     => 'Welcome',
                            'post_status'    => 'publish',
                            'post_type'      => 'page',
                            'ping_status'    => 'closed',
                            'comment_status' => 'closed',
                        )
                    );
                }
        }

        /**
         * Plugin deactivation hook.
         *
         */
        public static function plugin_deactivated() {
            // Not a good idea to remove roles after plugin deactivated as there might be users created with the new roles.       
            // Below file is to be emptied before production so that accidental deactivation of plugin will not delete away records.
            Myi_Inventory_Plugin::run_sql_script( '/assets/delete_tables.sql' );  // empty script, no tables deleted.      
        } // plugin activated      
       
        /**
         * Remove all the roles created by this plugin. Recommended to call it only upon plugin deletion.
         *
         */
        public static function remove_all_roles() {
            // remove all the roles
            remove_role( 'myi_user_manage');
        }
        
        /**
         * Add in the myi_user_manage roles.
         * if user roles is able to create/mod/delete user, will change user wp_role to this role.
         * Otherwise, will be using subscriber role.
         *
         */
        public static function add_the_roles() {
            // create the new roles. 

            add_role(
                        'myi_user_manage',
                        __( 'User Manage' ),
                        array(
                                'manage_network' => false,
                                'manage_sites' => false,
                                'manage_network_users' => false,
                                'manage_network_plugins' => false,
                                'manage_network_themes' => false,
                                'manage_network_options' => false,
                                'activate_plugins' => false,
                                'create_users' => true,
                                'delete_plugins' => false,
                                'delete_themes' => false,
                                'delete_users' => true,
                                'edit_files' => false,
                                'edit_plugins' => false,
                                'edit_theme_options' => false,
                                'edit_themes' => false,
                                'edit_users' => true,
                                'export' => false,
                                'import' => false,
                                'install_plugins' => false,
                                'install_themes' => false,
                                'list_users' => true,
                                'manage_options' => false,
                                'promote_users' => true,
                                'remove_users' => true,
                                'switch_themes' => false,
                                'update_core' => false,
                                'update_plugins' => false,
                                'update_themes' => false,
                                'edit_dashboard' => false,
                                'moderate_comments' => false,
                                'manage_categories' => false,
                                'manage_links' => false,
                                'edit_others_posts' => false,
                                'edit_pages' => false,
                                'edit_others_pages' => false,
                                'edit_published_pages' => false,
                                'publish_pages' => false,
                                'delete_pages' => false,
                                'delete_others_pages' => false,
                                'delete_published_pages' => false,
                                'delete_others_posts' => false,
                                'delete_private_posts' => false,
                                'edit_private_posts' => false,
                                'read_private_posts' => false,
                                'delete_private_pages' => false,
                                'edit_private_pages' => false,
                                'read_private_pages' => false,
                                'unfiltered_html' => false,
                                'edit_published_posts' => false,
                                'upload_files' => true,
                                'publish_posts' => false,
                                'delete_published_posts' => false,
                                'edit_posts' => false,
                                'delete_posts' => false,
                                'read' => true,
                        ) 
            );  // myi_user_manage  
        }
        
        /**
         * functions modified from exports_reports_reset() in exports and reports plugin
         *
         * Will run the sql command in the file passed in.
         * Each different command must be separated by ; followed by at least 2 CR/LF.
         * @param string $file filename of the sql commands relative to this file (my-inventory.php)
         */
        public static function run_sql_script( $file ) { 
            global $wpdb;

            $sql = file_get_contents( dirname( __FILE__ ) .$file );

            $charset_collate = 'DEFAULT CHARSET utf8';

            if ( !empty( $wpdb->charset ) ) {
                $charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
            }

            if ( !empty( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }

            if ( 'DEFAULT CHARSET utf8' != $charset_collate ) {
                $sql = str_replace( 'DEFAULT CHARSET utf8', $charset_collate, $sql );
            }

            $sql = explode( "@new_command@", str_replace( array( "\r", '@wp_@', '@cur_user_id@' ), array( "\n", $wpdb->prefix, get_current_user_id() ), $sql ) );

            for ( $i = 0, $z = count( $sql ); $i < $z; $i++ ) {
                $query = trim( $sql[ $i ] );

                if ( empty( $query ) ) {
                    continue;
                }
                
                // ignore the returned results
                $ignore = false;
                if ( substr( $query, 0, 8 ) == '@ignore@' ) {
                    $ignore = true;
                    $ignore_i = $i;
                    $query = substr( $query, 8 );
                }

                $my_result = $wpdb->query( $query );
                
                // store result in $check_result
                if ( $ignore ) {
                    $check_result = $my_result;
                }
                
                // if previous command has @ignore@ tag, then will ignore any message if $check_result is not valid
                // Eg, if reactivate and ALTER TABLE run on existing table, shall ignore any errors thrown by the alter table.
                //     but if first time run and ALTER TABLE fails, shall display error message
                if ( $my_result === false && !$ignore && !( $i == $ignore_i + 1 && !$check_result ) )  {
                    throw new \Exception('Error running sql statement - ' .$query);
                }
            }      
        }
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Inventory_Plugin already exists. Action aborted...'); 
} // Myi_Inventory_Plugin

// Initialize the plugin
$myi_inventory_plugin = new Myi_Inventory_Plugin();

// Create the custom pages at plugin activation
register_activation_hook( __FILE__, array( '\\my_inventory\\Myi_Inventory_Plugin', 'plugin_activated' ) );
register_deactivation_hook( __FILE__, array( '\\my_inventory\\Myi_Inventory_Plugin', 'plugin_deactivated' ) );

add_action('after_setup_theme', '\\my_inventory\\myi_remove_admin_bar'); 

add_filter( 'wp_nav_menu_items', '\\my_inventory\\myi_add_menus', 10, 2 );
// load in css using priority 99999 in the hope that it will load in last
add_action( 'wp_enqueue_scripts', '\\my_inventory\\myi_inventory_enqueue_styles', 99999 );
add_action( 'wp_enqueue_scripts', '\\my_inventory\\myi_theme_scripts' );

register_uninstall_hook( __FILE__, '\\my_inventory\\myi_uninstall' );

add_action( 'admin_bar_menu', '\\my_inventory\\myi_toolbar_link_to_mypage', 999 );

add_action('after_setup_theme', '\\my_inventory\\myi_add_primary_menu'); 

/* for cut and paste
	require_once( plugin_dir_path( __FILE__ ) .'/../myi_uom_record.php');
	require_once( plugin_dir_path( __FILE__ ) .'/../myi_inventory.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_user_client_roles.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_client.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_product.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_location.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_uom.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_category.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_product_category.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_user.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_log.php');
    require_once( plugin_dir_path( __FILE__ ) .'/../myi_report.php');
*/