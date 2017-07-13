<?php
namespace my_inventory;
/**
*  Add in the menu items
*  if the theme is not using theme_location 'primary' for the menu, hook into action myi_add_menus_theme_location and change the 
*   $theme_location variable to the correct location. Otherwise, the 'Login' and 'Logout' menu will not appear
*
*  @param string $items the lists of current menu items. Will add on to this list
*  @param object $args An object containing wp_nav_menu() arguments.
*  @return string the codes for the navigation menu
*/
if ( ! function_exists( '\\my_inventory\\myi_add_primary_menu' ) ) {
    function myi_add_primary_menu() {
        if ( ! is_nav_menu( 'primary' ) ) {
            $menu_id = wp_create_nav_menu ( 'primary' );
            
            // Lower case theme_name
            $theme = strtolower ( str_replace ( ' ', '_', wp_get_theme() ) );
     
            // Get the theme options
            $theme_mods = get_option ( 'theme_mods_' . $theme );
     
            // Set the location of the primary menu
            $theme_mods['nav_menu_locations'] = array ( 'primary' => $menu_id );
     
            // Update the theme options
            update_option ( 'theme_mods_' . $theme, $theme_mods );
        }    
    }
}

/**
*  Add in the menu items
*  if the theme is not using theme_location 'primary' for the menu, hook into action myi_add_menus_theme_location and change the 
*   $theme_location variable to the correct location. Otherwise, the 'Login' and 'Logout' menu will not appear
*
*  @param string $items the lists of current menu items. Will add on to this list
*  @param object $args An object containing wp_nav_menu() arguments.
*  @return string the codes for the navigation menu
*/
if ( ! function_exists( '\\my_inventory\\myi_add_menus' ) ) {
    function myi_add_menus( $items, $args ) {                        
        // if using my-login plugin which allow different landing pages for each client to login to same system.
        // my-login is not released to the public.
        if ( in_array( 'my-login/my-login.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {         
            $client_arg = ( isset($_GET['client']) ? urlencode(stripslashes($_GET['client'])) : null );
            $login_url = (!isset($_GET['client']) ? 'member' : urlencode(stripslashes($_GET['client']))) .'-login';
            $logout_url = wp_logout_url(add_query_arg( 'client', $client_arg, $login_url ));
        } else {
            $client_arg = null;
            $login_url = 'wp-login.php';
            $logout_url = wp_logout_url();
        }
        
        // only show log in/out and menus for pages that is not login, password-reset, lost-password
        if  ( substr( strtolower(get_queried_object()->post_name), -5 ) != 'login' &&
              substr( strtolower(get_queried_object()->post_name), -14 ) != 'password-reset' &&
              substr( strtolower(get_queried_object()->post_name), -13 ) != 'password-lost' ) {
            $user_roles = new Myi_User_Roles();
            $rights = $user_roles->get_access_rights( get_current_user_id() );


            $url_arg = ( is_null($client_arg) ? '' : '?client=' .$client_arg );

            // administrator can access this menu no matter what
            if ( !( strpos($rights, ',create_user,') === false ) || 
                 !( strpos($rights, ',mod_user,') === false ) ||  
                 !( strpos($rights, ',delete_user,') === false ) ||
                 !( strpos($rights, ',mod_roles,') === false ) ||
                 current_user_can( 'create_users' )) {
                $items .= '<li class="dropdown"><a class="dropdown-toggle level1" data-toggle="dropdown" href="#">Users<span class="caret"></span></a><ul class="dropdown-menu">';
                
                //------------------------
                // User menu
                //------------------------
                
                // Add User
                if ( !( strpos($rights, ',create_user,') === false ) || 
                        current_user_can( 'create_users' ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-user/' .$url_arg .'">Add User</a></li>';
                }

                // Edit User
                if ( !( strpos($rights, ',mod_user,') === false ) ||
                     !( strpos($rights, ',mod_roles,') === false ) ||
                     current_user_can( 'create_users' )) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-user/' .$url_arg .'">Edit User</a></li>';
                }
                
                // Delete User
                if ( !( strpos($rights, ',delete_user,') === false ) ||
                        current_user_can( 'create_users' )) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-user/' .$url_arg .'">Delete User</a></li>';
                }
                
                // Assign Roles
                  if ( !( strpos($rights, ',mod_roles,') === false ) ||
                        current_user_can( 'create_users' )) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-assign-roles/' .$url_arg .'">Assign Roles</a></li>';
                }

                // Unassign Roles
                  if ( !( strpos($rights, ',mod_roles,') === false ) ||
                        current_user_can( 'create_users' )) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-unassign-roles/' .$url_arg .'">Unassign Roles</a></li>';
                }

                // Copy Roles
                  if ( !( strpos($rights, ',mod_roles,') === false ) ||
                        current_user_can( 'create_users' )) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-copy-roles/' .$url_arg .'">Copy Roles</a></li>';
                }

                //------------------------
                // Menu finishing
                //------------------------
                $items .= '</ul></li>';
            }

            if ( !( strpos($rights, ',view_inventory_master,') === false ) || 
                 !( strpos($rights, ',create_inventory_master,') === false ) ||
                 !( strpos($rights, ',mod_inventory_master,') === false ) ||
                 !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                $items .= '<li class="dropdown"><a class="dropdown-toggle level1" data-toggle="dropdown" href="#">Masters Setup<span class="caret"></span></a><ul class="dropdown-menu">';
                
                //------------------------
                // client menu
                //------------------------
                $items .= '<li><a class="level2" href="#">Clients<span class="caret"></span></a><ul class="dropdown-submenu">';
                
                // view client
                if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-client/' .$url_arg .'">View Client</a></li>';
                }
                
                // if in client page (eg cme-login etc), disallow clients add and delete
                if (!isset($_GET['client'])) {
                    // add new client
                    if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-client/' .$url_arg .'">Add New Client</a></li>';
                    }
                    
                    // delete client
                    if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-client/' .$url_arg .'">Delete Client</a></li>';
                    }       
                } //(!isset($_GET['client']))
                
                // mod client
                if ( !( strpos($rights, ',mod_inventory_master,') === false ) ) {
                           $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-client/' .$url_arg .'">Edit Client</a></li>';
                }

                $items .= '</ul></li>';
                
                //------------------------
                // Product menu
                //------------------------
                $items .= '<li><a class="level2" href="#">Products<span class="caret"></span></a><ul class="dropdown-submenu">';
                
                // view Product
                if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-prod/' .$url_arg .'">View Product</a></li>';
                }
                
                // add new Product
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-prod/' .$url_arg .'">Add New Product</a></li>';
                }
                
                // mod Product
                if ( !( strpos($rights, ',mod_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-prod/' .$url_arg .'">Edit Product</a></li>';
                }

                // delete Product
                if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-prod/' .$url_arg .'">Delete Product</a></li>';
                }                 
                
                $items .= '</ul></li>';
                
                //------------------------
                // UOM menu
                //------------------------
                $items .= '<li><a class="level2" href="#">Units Of Measure<span class="caret"></span></a><ul class="dropdown-submenu">';
                
                // view UOM
                if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-uom/' .$url_arg .'">View UOM</a></li>';
                }
                
                // add new UOM
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-uom/' .$url_arg .'">Add New UOM</a></li>';
                }
                
                // mod UOM
                if ( !( strpos($rights, ',mod_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-uom/' .$url_arg .'">Edit UOM</a></li>';
                }

                // delete UOM
                if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-uom/' .$url_arg .'">Delete UOM</a></li>';
                }  

                $items .= '</ul></li>';                

                //------------------------
                // Category menu
                //------------------------
                $items .= '<li><a class="level2" href="#">Categories<span class="caret"></span></a><ul class="dropdown-submenu">';
                
                // view Category
                if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-cat/' .$url_arg .'">View Category</a></li>';
                }
                
                // add new Category
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-cat/' .$url_arg .'">Add New Category</a></li>';
                }
                
                // mod Category
                if ( !( strpos($rights, ',mod_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-cat/' .$url_arg .'">Edit Category</a></li>';
                }

                // delete Category
                if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-cat/' .$url_arg .'">Delete Category</a></li>';
                }                  
                
                $items .= '</ul></li>';

                //------------------------
                // Location menu
                //------------------------
                $items .= '<li><a class="level2" href="#">Locations<span class="caret"></span></a><ul class="dropdown-submenu">';
                
                // view Location
                if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-loc/' .$url_arg .'">View Location</a></li>';
                }
                
                // add new Location
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-loc/' .$url_arg .'">Add New Location</a></li>';
                }
                
                // mod Location
                if ( !( strpos($rights, ',mod_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-mod-loc/' .$url_arg .'">Edit Location</a></li>';
                }

                // delete Location
                if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                        $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-loc/' .$url_arg .'">Delete Location</a></li>';
                }                 
                
                $items .= '</ul></li>';
                
                //------------------------
                // Prod Category menu
                //------------------------
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ||
                    !( strpos($rights, ',delete_inventory_master,') === false ) ) {
          
                    $items .= '<li><a class="level2" href="#">Prod to Cat Linkages<span class="caret"></span></a><ul class="dropdown-submenu">';
                    
                    // view Prod Linkage
                    if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-cat-link/' .$url_arg .'">View Cat/Prod Link</a></li>';
                    }
                    
                    // Link Product to Category
                    if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-link-cat/' .$url_arg .'">Put Prod in Cat</a></li>';
                    }
                    
                    // unlink Product from Category
                    if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-unlink-cat/' .$url_arg .'">Remove Prod from Cat</a></li>';
                    }
                                    
                    $items .= '</ul></li>';    
                }

                //------------------------
                // Uoms for Prod
                //------------------------
                if ( !( strpos($rights, ',create_inventory_master,') === false ) ||
                    !( strpos($rights, ',delete_inventory_master,') === false ) ||
                    !( strpos($rights, ',view_inventory_master,') === false )) {
                        
                    $items .= '<li><a class="level2" href="#">Setup Product UOMs<span class="caret"></span></a><ul class="dropdown-submenu">';
                    
                    // view Prod UOMS
                    if ( !( strpos($rights, ',view_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-prod-uom/' .$url_arg .'">View Prod UOMs</a></li>';
                    }
                    
                    // add new Prod UOMS
                    if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-add-prod-uom/' .$url_arg .'">Add UOMs to Prod</a></li>';
                    }
                    
                    // copy Prod UOMS
                    if ( !( strpos($rights, ',create_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-copy-prod-uom/' .$url_arg .'">Copy UOMs</a></li>';
                    }                    
                    
                    // delete Prod UOMS
                    if ( !( strpos($rights, ',delete_inventory_master,') === false ) ) {
                            $items .= '<li><a class="level3" href="' .home_url() .'/myi-del-prod-uom/' .$url_arg .'">Remove UOM sets</a></li>';
                    }                  
                    
                    $items .= '</ul></li>';     
                }
                
                //------------------------
                // Menu finishing
                //------------------------
                $items .= '</ul></li>';
            }

            if ( !( strpos($rights, ',stock_mod_inventory,') === false ) || 
                 !( strpos($rights, ',view_inventory,') === false ) ) {
                $items .= '<li class="dropdown"><a class="dropdown-toggle level1" data-toggle="dropdown" href="#">Stocks<span class="caret"></span></a><ul class="dropdown-menu">';
                
                //------------------------
                // stocks menu
                //------------------------
                
                // View Stocks
                if ( !( strpos($rights, ',view_inventory,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-view-stock/' .$url_arg .'">View Stocks</a></li>';
                }

                // Stock In/Out
                // Transfer Stock
                if ( !( strpos($rights, ',stock_mod_inventory,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-stock-in/' .$url_arg .'">Stock In/Out</a></li>';
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-stock-trans/' .$url_arg .'">Transfer Stock</a></li>';
                }

                //------------------------
                // Menu finishing
                //------------------------
                $items .= '</ul></li>';
            }

            if ( !( strpos($rights, ',view_logs,') === false ) ) {
                $items .= '<li class="dropdown"><a class="dropdown-toggle level1" data-toggle="dropdown" href="#">Logs<span class="caret"></span></a><ul class="dropdown-menu">';
                
                //------------------------
                // Logs menu
                //------------------------
                
                // Inventory
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-inv-log/' .$url_arg .'">Inventory Logs</a></li>';
                }

                // Category
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-cat-log/' .$url_arg .'">Category Logs</a></li>';
                }

                // Category Products
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-cat-prod-log/' .$url_arg .'">Category Linkage Logs</a></li>';
                }

                // client
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-client-log/' .$url_arg .'">Client Logs</a></li>';
                }
                
                // Location
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-loc-log/' .$url_arg .'">Location Logs</a></li>';
                }
                
                // Product
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-prod-log/' .$url_arg .'">Product Logs</a></li>';
                }

                // UOM
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-uom-log/' .$url_arg .'">UOM Logs</a></li>';
                }

                // Prod UOM
                if ( !( strpos($rights, ',view_logs,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-prod-uom-log/' .$url_arg .'">Product UOM Logs</a></li>';
                }

                //------------------------
                // Menu finishing
                //------------------------
                $items .= '</ul></li>';
            }

            if ( !( strpos($rights, ',view_reports,') === false ) ) {
                $items .= '<li class="dropdown"><a class="dropdown-toggle level1" data-toggle="dropdown" href="#">Reports<span class="caret"></span></a><ul class="dropdown-menu">';
                
                //------------------------
                // Reports menu
                //------------------------
                
                // Stock Count
                if ( !( strpos($rights, ',view_reports,') === false ) ) {
                    $items .= '<li><a class="level3" href="' .home_url() .'/myi-inv-rpt/' .$url_arg .'">Stock Counts</a></li>';
                }

                //------------------------
                // Menu finishing
                //------------------------
                $items .= '</ul></li>';
            }
            
            $theme_location = 'primary';
            
            do_action( 'myi_add_menus_theme_location' );
            
            if ( is_user_logged_in() && $args->theme_location == $theme_location ) {
                $items .= '<li class="dropdown"><a tabindex="-1" href="'.$logout_url .'">Log Out</a></li>';
            }
            elseif ( !is_user_logged_in() && $args->theme_location == $theme_location ) {
                $items .= '<li class="dropdown"><a tabindex="-1" href="'. site_url($login_url) .'">Log In</a></li>';
            }
        }
        
        if ( is_user_logged_in() ) {
            $items .= '<br>Welcome, ' . wp_get_current_user()->display_name;
        }
        
        return $items;
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_add_menus already exists. Action aborted...'); 
} // myi_add_menus

?>