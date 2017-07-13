<?php
namespace my_inventory;

/**
*  Get the current Date in Y-m-d H:i:s format. If no timezone is passed in, will default to Asia/Singapore
*
*  @param string $strTimeZone the timezone
*  @return string the current datetime in Y-m-d H:i:s format
*/
if ( ! function_exists( '\\my_inventory\\myi_getDatetimeNow' ) ) {
    function myi_getDatetimeNow($strTimeZone = "Asia/Singapore") {
        $tz_object = new \DateTimeZone($strTimeZone);

        $datetime = new \DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y\-m\-d\ H:i:s');
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_getDatetimeNow already exists. Action aborted...'); 
} // myi_getDatetimeNow


/**
*  Remove the admin bar except for administrator. Thus will look like a normal web application (except for the admininstrator
*
*/
if ( ! function_exists( '\\my_inventory\\myi_remove_admin_bar' ) ) {
    function myi_remove_admin_bar() {
        if (!current_user_can('administrator') && !is_admin()) {
          show_admin_bar(false);
        }
    }           
} else {
    throw new \Exception('Function \\my_inventory\\myi_remove_admin_bar already exists. Action aborted...'); 
} // myi_remove_admin_bar

/**
*  retrieves the attachment ID from the file URL
*  Author is Pippin
*
*  @param string $image_url the url of the image
*  @return string the image attachment ID (Note that in wp database, ID is bigint)
*/
if ( ! function_exists( '\\my_inventory\\myi_get_image_id' ) ) {
    function myi_get_image_id($image_url) {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
            return $attachment[0]; 
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_get_image_id already exists. Action aborted...'); 
} //myi_get_image_id

/**
*  create admin menu to direct to member-account page
*
*  @param string $wp_admin_bar the codes for the admin bar
*/
if ( ! function_exists( '\\my_inventory\\myi_toolbar_link_to_mypage' ) ) {
    function myi_toolbar_link_to_mypage( $wp_admin_bar ) {
        $args = array(
            'id'    => 'my_inv',
            'title' => 'My Inventory Page',
            'href'  => home_url() .'/member-account/',
        );
        $wp_admin_bar->add_node( $args );
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_toolbar_link_to_mypage already exists. Action aborted...'); 
} // myi_toolbar_link_to_mypage

/**
*  convert the datetime to YYYY-MM-DD HH24:MM:SS format
*  To change the $input_date_format and $output_format, hook into action myi_convert_date_set_date_format and change
*   $input_date_format and/or $output_format
*
*  @param string $date the date to convert. The date shall be in d/m/Y h:i A format
*  @param bool $is_start_date if true will set sec to 00 else set to 59. if false, will mean that it is end_date. Default is start date
*  @param string $output_format default is YYYY-MM-DD HH24:MM:SS. (Y-m-d H:i:s)
*  @return string the date in YYYY-MM-DD HH24:MM:SS format
*/
if ( ! function_exists( '\\my_inventory\\myi_convert_date' ) ) {
    function myi_convert_date( $date, $is_start_date = null, $output_format = null) {
            if ( $date === null || trim($date) == '') {
                    return null;
            }
            
            $input_date_format = 'd\/m\/Y h\:i A';
            
            if ( $output_format === null ) {
                $output_format = 'Y-m-d H:i:s';
            }

            do_action( 'myi_convert_date_set_date_format' );

            $newDate = \DateTime::createFromFormat( $input_date_format, trim($date) );

            // set seconds to zero if it is start date else set to 59 if end date
            if ( $is_start_date === null || $is_start_date ) {
                $sec = 0;
            } else {
                $sec = 59;
            }
            
            $newDate->setTime( $newDate->format('H'), $newDate->format('i'), $sec );
            return $newDate->format($output_format);
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_convert_date already exists. Action aborted...'); 
} // myi_convert_date

/**
*  enqueue the stylesheet
*
*/
if ( ! function_exists( '\\my_inventory\\myi_inventory_enqueue_styles' ) ) {
	function myi_inventory_enqueue_styles() {
        //enqueue bootstrap select
        wp_enqueue_style( 'bootstrap-select', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.0/css/bootstrap-select.min.css' );
        //plugin stylesheet
        wp_enqueue_style( 'my-inventory', plugins_url() . '/my-inventory/css/style.css' );
	}
} else {
    throw new \Exception('Function \\my_inventory\\myi_inventory_enqueue_styles already exists. Action aborted...'); 
} // myi_inventory_enqueue_styles

/**
*  actions to take upon uninstalling the plugin
*  basically it will delete away the tables this plugins create and remove the roles this plugin create
*
*/
if ( ! function_exists( '\\my_inventory\\myi_uninstall' ) ) {
    function myi_uninstall() {
        // delete away the databases created by myi
        Myi_Inventory_Plugin::run_sql_script( '/assets/delete_tables_dev.sql' );   
        
        // remove all roles
        Myi_Inventory_Plugin::remove_all_roles();
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_uninstall already exists. Action aborted...'); 
}

/**
*  enqueue the script
*  this will enqueue mainly the js and also any css used by that plugin
*
*/
if ( ! function_exists( '\\my_inventory\\myi_theme_scripts' ) ) {
    function myi_theme_scripts() {
        global $post;
        $logs_js_loc = plugins_url() . '/my-inventory/js/myi-logs.js';
        $rpts_js_loc = plugins_url() . '/my-inventory/js/myi-reports.js';
        
        do_action('change_logs_rpts_js_loc');
        
        //enqueue bootstrap select
        wp_enqueue_script( 'bootstrap-select-script', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.0/js/bootstrap-select.min.js', array( 'jquery' ), '1.11.0', true );
        //enqueue jQuery Validation
        wp_enqueue_script( 'jquery-validation', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.1/jquery.validate.min.js' );
        //plugin script
        wp_enqueue_script( 'my-inventory-script', plugins_url() . '/my-inventory/js/jQuery.js', array( 'jquery' ), '1.0.0', true );

        if( is_page() )
        {
                switch($post->post_name) 
                {
                    case 'myi-add-prod-uom':
                        wp_enqueue_script( 'my-inventory-script2', plugins_url() . '/my-inventory/js/myi-add-prod-uom.js', array( 'jquery' ), '1.0.0', true );
                        break;
                    case 'myi-copy-prod-uom':
                        wp_enqueue_script( 'my-inventory-script3', plugins_url() . '/my-inventory/js/myi-copy-prod-uom.js', array( 'jquery' ), '1.0.0', true );
                        break;
                }
                
                switch(substr($post->post_name,-3)) //ends with
                {
                    case 'log':
                    case 'rpt':
                        wp_enqueue_script( 'my-inventory-script100', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.14.1/moment-with-locales.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script101', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.14.1/locale/en-au.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script105', '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script110', '//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script110b', '//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script111', '//cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script112', '//cdn.datatables.net/buttons/1.2.2/js/buttons.bootstrap.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script113', '//cdn.datatables.net/responsive/2.1.0/js/dataTables.responsive.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script114', '//cdn.datatables.net/responsive/2.1.0/js/responsive.bootstrap.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script120', '//cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script121', '//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script122', '//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script123', '//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script124', '//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js', array( 'jquery' ), '1.0.0', true );
                        wp_enqueue_script( 'my-inventory-script125', '//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js', array( 'jquery' ), '1.0.0', true );

                        wp_enqueue_style( 'my-inventory-105', '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css' );
                        wp_enqueue_style( 'my-inventory-110', '//cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css' );
                        wp_enqueue_style( 'my-inventory-111', '//cdn.datatables.net/buttons/1.2.2/css/buttons.bootstrap.min.css' );
                        wp_enqueue_style( 'my-inventory-113', '//cdn.datatables.net/responsive/2.1.0/css/responsive.bootstrap.min.css' );
                        break;
                }
                
                switch(substr($post->post_name,-3)) //ends with. Everything is same for log and rpt except for the jquery script
                {
                    case 'log': wp_enqueue_script( 'my-inventory-script115', $logs_js_loc, array( 'jquery' ), '1.0.0', true );
                                break;
                    case 'rpt': wp_enqueue_script( 'my-inventory-script115', $rpts_js_loc, array( 'jquery' ), '1.0.0', true );
                                break;
                }
        }        
    }
} else {
    throw new \Exception('Function \\my_inventory\\myi_theme_scripts already exists. Action aborted...'); 
} // myi_theme_scripts
?>