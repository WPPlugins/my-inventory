=== Plugin Name ===
Contributors: drago888
Tags: inventory, free, stock counts
Requires at least: 3.0.1 (Only tested on version 4.6.1)
Tested up to: 4.6.1
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

A simple Inventory management system that allows from 10-19 levels of UOM.   
  
Eg. Carton <- Box <- Piece will be 3 levels of UOM.   
Meaning that 1 carton will contain X number of boxes and 1 box will contain Y number of piece.   
  
Only tested for iribbon and ocin-lite themes.   
   
**Plugin Dependencies :** WordPress Twitter Bootstrap (from iControlWP), Insert PHP     
  
**Non Wordpress plugin dependencies :** Bootstrap 3, Bootstrap select, JQuery Validation, Bootstrap Datatables   
  
Bootstrap shall be using version 3.  
  
Below is a description of the functionality allowed for each user roles rights   
  
*view_inventory* - can see/select from the inventory,    

*stock_mod_inventory* - can add/remove stocks from inventory    

*create_inventory* - can create new inventory (this is only for txt table)    

*mod_inventory* - can modify current inventory (this is only for txt table),    

*delete_inventory* - can delete inventory (this is only for txt table),    

*view_inventory_master* - can see/select from any master tables (starts with @wp_@_myi_mst. note that @wp_@ is the wp table prefix)    

*mod_inventory_master* - can modify any of the mst tables,    

*create_inventory_master* - can create any of the mst tables,    

*delete_inventory_master* - can delete any of the mst tables,    

*create_user* - can create a new user    

*mod_user* - can modify the new user    

*delete_user* - can delete the user. Note that user is not physically deleted in wp_users tables. To physically delete, use wordpress dashboard users.  
                please take note that if physically deleting the user might break any past logs/transaction    

*mod_roles* - can modify the roles for any users. Do not modify the administrator as it will change the administrator role.  

*view_logs* - can view the logs    

*view_reports* - can view the reports    

    
  when enqueue css for child theme, remember to set priority to more than 99999 so that it will load after the plugin  
  
  
Take note when deleting plugin, all databases and wordpress roles created by the plugin will be deleted. Thus ensure that all the users   
are not using any roles starting with myi. Only users that had their roles changed in the application will have any chance of their wordpress role been changed.
  
When using ocin-lite theme, remember to change the submenu background color in css to non white so that can see the wordings.

== Installation ==

Download the zip file and unzip into your wordpress wp-content/plugins directory.  
Go to the wordpress dashboard (plugins) and activate it.  



== Frequently Asked Questions ==

= How do I change the default client_cd? =

To change the default client_cd, go to myi-inventory/assets/setup_tables.sql and modify the statement   

INSERT INTO `@wp_@myi_mst_client` (`client_id`, `client_cd`, `client_name`, `client_remark`, `deleted`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id`, `delete_date`, `delete_by_id`) 
VALUES (NULL, 'Default', 'Default', NULL, 0, now(), @cur_user_id@, now(), @cur_user_id@, NULL, NULL);  
  
to  
  
INSERT INTO `@wp_@myi_mst_client` (`client_id`, `client_cd`, `client_name`, `client_remark`, `deleted`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id`, `delete_date`, `delete_by_id`) 
VALUES (NULL, 'Name I want', 'Default', NULL, 0, now(), @cur_user_id@, now(), @cur_user_id@, NULL, NULL);  
  
then rename myi-inventory/assets/delete_tables.sql to myi-inventory/assets/delete_tables_prod.sql  
followed by rename myi-inventory/assets/delete_tables_dev.sql to myi-inventory/assets/delete_tables.sql  
  
Deactivate the plugin and then reactivate it.  
  
Followed by the below to revert back (in case accidentally click on deactivate and all tables deleted)  
  
rename myi-inventory/assets/delete_tables.sql to myi-inventory/assets/delete_tables_dev.sql  
rename myi-inventory/assets/delete_tables_prod.sql to myi-inventory/assets/delete_tables.sql  
  
= How do I get my theme css to take precedence over the css of the plugin? = 

For any themes css to take precedence over the plugin, set the priority of the theme to higher than 99999.  
eg.   
// set priority to 999999 so that will load after plugin  
`add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles',999999 );  
if ( ! function_exists( 'my_theme_enqueue_styles' ) ) {  
	function my_theme_enqueue_styles() {  
    		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );  
    		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style-rtl.css' );  
  
		    $parent_style = 'parent-style';  
  
    		wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );  
    		wp_enqueue_style( 'child-style',  
        		get_stylesheet_directory_uri() . '/style.css',  
        		array( $parent_style )  
    		);  
	}  
}`  

= Can I transfer the same products into different packings? = 

So long as it is the same product with the same smallest UOM, you can transfer between each other.

Eg. Product A have the below UOM Sets.   

 (1.)  Box <- (X50) <- Packages  
   
 (2.)  Box <- (X100) <- Packages   
   
 (3.)  Box <- (X1000) <- Pieces   
  
You can transfer between (1.) and (2.) as both have the same smallest UOM (Packages).  
However, you can't do it for (3.)  
In order to transfer from (1.) to (3.), you need to add in the number of pieces per package for (1.)  
  
= Why can't I delete the product/UOM/Category etc? = 

Ensure that there are no longer any inventory for that product.   
Ensure that the product/UOM/Category is no longer in use. (also not set in Prod UOM Setup).   

= Hey!!! Some codes in this plugin belongs to me =

I try to give proper credit to all the Authors of my codes.   
However, at times (especially when rushing dateline), I might have forgotten to give you proper credit.   
  
Please forgive me and drop me an email at [ng.kock.leong@elinemm.com](mailto:ng.kock.leong@elinemm.com) indicating the function name and the author name.  
I will add into the codes the proper author of the function.

= Who is the photographer for the plugin header (the warehouse photo)? =

The photo belongs to [Ronnieb](https://morguefile.com/creative/ronnieb).  
You can find the photo at [https://morguefile.com/search/morguefile/2/shelves/pop](https://morguefile.com/search/morguefile/2/shelves/pop).

= I can't see the menu items =

Ensure that you have created a menu named primary.

== Changelog ==

= 1.0 =
* Initial rollout

= 1.0.1 = 
* Add in member-account page if not exists

= 1.0.2 = 
* Prefix myi to all functions 
* change plugin class from my_inventory_plugin to myi_inventory_plugin to standardise all prefixes as myi
* add namespace for everything. Everything shall be at least \my_inventory namespace
* throw error if functions/classes already exists for same namespace and classes/functions. This is to prevent other plugins breaking this plugin.
* change all path to use plugins_dir_path
* move all the functions to external files
* rename function pippin_get_image_id to myi_get_image_id
* rename function remove_admin_bar to myi_remove_admin_bar
* rename and move function km_get_user_role to myi_get_user_role and move to myi_main_unused_functions.php files
* remove the codes for the previous wordpress user roles which are no longer in use
* change all path with wp-content to use plugin_dir_url( __FILE__ ) instead

= 1.0.3 =
* update readme.txt to correct some incorrect wordings and change Requires at least.
* add in the screenshots

= 1.0.4 =
* Add in codes to throw error when the processing of the sql files failed. (eg tables or functions not created)
* Change the function that process sql statement to treat ; followed by 4 line breaks to treat as new command as WP SVN will alter the sql files and insert additional linebreaks

= 1.0.5 =
* Modify the codes to use @new_command@ to determine a new command instead of using line breaks which are not working consistently.

= 1.0.6 =
* add in handling of @ignore@ tag for sql files. This tag will not raise any error for this statement and if this statement fails, next statement will not raise any errors. Meant to be used for the CREATE TABLE command before the ALTER TABLE command. Needed so that reactivation will not raise error but first run will raise error if SQL failed.

= 1.0.7 =
* add in codes to create primary menu for current theme if it does not exists.

== Upgrade Notice == 

= 1.0 =
* Initial rollout

== Screenshots ==

1. Searchable Product List
2. Store Manager Menu List
3. Menu - User
4. Menu - Master Setup -> Client
5. Menu - Master Setup -> Product
6. Menu - Master Setup -> Unit Of Measure (UOM)
7. Menu - Master Setup -> Category
8. Menu - Master Setup -> Location
9. Menu - Master Setup -> Product to Category Linkage
10. Menu - Master Setup -> Setup Product UOMs
11. Menu - Stocks
12. Menu - Logs
13. Menu - Reports
14. Stock Count Reports selection