<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Log' ) ) { 
    class Myi_Log {  
        public function __construct() {     
        }

        /**
        *  Get the inventory Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $client the list of clients. If empty or 1st element id is 0, will get all
        *  @param array of string $prod the list of product. If empty or 1st element id is 0, will get all
        *  @param array of string $loc the list of location. If empty or 1st element id is 0, will get all
        *  @param array of string $client_for the list of clients_for. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param int @caller_user_id user_id of person running the log
        *  @return object/int if successful, false if fail
        */  
        public function get_inventory( $date_from, $date_to, $client, $prod, $loc,
                                            $client_for, $user, $caller_user_id ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                if ( a.smallest_uom_qty >= 0, \'Stock in\', \'Stock Out\') as action,
                                f.client_cd,
                                g.prod_cd,
                                a.desc as uom_set_desc,
                                d.location_cd,
                                e.client_cd as for_client,
                                a.job_no,
                                a.remarks,
                                a.`add-field1`,
                                a.`add-field2`,
                                a.`add-field3`,
                                a.`add-field4`,
                                a.`add-field5`,
                                concat('    .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-9`),if(abs(a.`qty-l-9`) > 1, a.`l-9_uom_short_p`, a.`l-9_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-8`),if(abs(a.`qty-l-8`) > 1, a.`l-8_uom_short_p`, a.`l-8_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-7`),if(abs(a.`qty-l-7`) > 1, a.`l-7_uom_short_p`, a.`l-7_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-6`),if(abs(a.`qty-l-6`) > 1, a.`l-6_uom_short_p`, a.`l-6_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-5`),if(abs(a.`qty-l-5`) > 1, a.`l-5_uom_short_p`, a.`l-5_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-4`),if(abs(a.`qty-l-4`) > 1, a.`l-4_uom_short_p`, a.`l-4_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-3`),if(abs(a.`qty-l-3`) > 1, a.`l-3_uom_short_p`, a.`l-3_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-2`),if(abs(a.`qty-l-2`) > 1, a.`l-2_uom_short_p`, a.`l-2_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l-1`),if(abs(a.`qty-l-1`) > 1, a.`l-1_uom_short_p`, a.`l-1_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l0`),if(abs(a.`qty-l0`) > 1, a.`l0_uom_short_p`, a.`l0_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l1`),if(abs(a.`qty-l1`) > 1, a.`l1_uom_short_p`, a.`l1_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l2`),if(abs(a.`qty-l2`) > 1, a.`l2_uom_short_p`, a.`l2_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l3`),if(abs(a.`qty-l3`) > 1, a.`l3_uom_short_p`, a.`l3_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l4`),if(abs(a.`qty-l4`) > 1, a.`l4_uom_short_p`, a.`l4_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l5`),if(abs(a.`qty-l5`) > 1, a.`l5_uom_short_p`, a.`l5_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l6`),if(abs(a.`qty-l6`) > 1, a.`l6_uom_short_p`, a.`l6_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l7`),if(abs(a.`qty-l7`) > 1, a.`l7_uom_short_p`, a.`l7_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l8`),if(abs(a.`qty-l8`) > 1, a.`l8_uom_short_p`, a.`l8_uom_short`)),'
                                            .$wpdb->prefix . 'myi_udf_get_qty_uom(abs(a.`qty-l9`),if(abs(a.`qty-l9`) > 1, a.`l9_uom_short_p`, a.`l9_uom_short`))) as qty_uom';
                                            
                                
            $select_body =' FROM `' .$wpdb->prefix .'myi_vw_inv_uoms` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        LEFT JOIN `' .$wpdb->prefix .'myi_mst_location` d
                            on a.location_id = d.location_id
                        LEFT JOIN `' .$wpdb->prefix .'myi_mst_client` e /* client for */
                            on a.client_id_for = e.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_client` f /* client */
                            on a.client_id = f.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_product` g
                            on a.prod_id = g.prod_id
                        INNER JOIN (    SELECT max(`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` b 
                                            on a.role_id = b.role_id
                                            and a.deleted = 0
                                        WHERE a.user_id = %d
                                        group by a.client_id
                                        union
                                        /* get all the other clients that dun have records and use settings for client_id = 1 */
                                        SELECT max(d.`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_client` a 
                                        left join `' .$wpdb->prefix .'myi_mst_user_client_role` b 
                                            on a.client_id = b.client_id
                                            and a.deleted = 0
                                            and b.deleted = 0
                                            and b.user_id = %d
                                        inner join ( select *
                                                     from `' .$wpdb->prefix .'myi_mst_user_client_role` 
                                                     where client_id = 1
                                                        and user_id = %d
                                                        and deleted = 0 ) c 
                                            on c.client_id = 1
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` d 
                                            on c.role_id = d.role_id
                                        WHERE b.role_id is null
                                        group by a.client_id
                                    ) cc 
                            on cc.view_logs = 1
                               and cc.client_id = a.client_id';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';
                
                foreach ($client as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }
            
            // prod
            if ( $prod === null || count($prod) == 0 || $prod[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.prod_id in ( 0';
                
                foreach ($prod as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // loc
            if ( $loc === null || count($loc) == 0 || $loc[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.location_id in ( 0';

                foreach ($loc as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // client_for
            if ( $client_for === null || count($client_for) == 0 || $client_for[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id_for in ( 0';

                foreach ($client_for as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_inventory

        /**
        *  Get the category Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $category the list of category. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param string $user_roles user_roles
        *  @return object/int if successful, false if fail
        */  
        public function get_category( $date_from, $date_to, $category, $user, $user_roles ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                d.cat_cd,
                                a.cat_name,
                                a.cat_img_url';
                                            
                                
            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_category` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_category` d
                            on a.cat_id = d.cat_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                            on c.view_logs = 1';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // category
            if ( $category === null || count($category) == 0 || $category[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.cat_id in ( 0';
                
                foreach ($category as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_category

        /**
        *  Get the product category Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $client the list of clients. If empty or 1st element id is 0, will get all
        *  @param array of string $prod the list of product. If empty or 1st element id is 0, will get all
        *  @param array of string $category the list of categories. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param int @caller_user_id user_id of person running the log
        *  @return object/int if successful, false if fail
        */  
        public function get_prod_cat( $date_from, $date_to, $client, $prod, $category,
                                            $user, $caller_user_id ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                f.client_cd,
                                g.prod_cd,
                                d.cat_cd';
                                            
                                
            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_category_product` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_category` d
                            on a.cat_id = d.cat_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_client` f 
                            on a.client_id = f.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_product` g
                            on a.prod_id = g.prod_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` b 
                                            on a.role_id = b.role_id
                                            and a.deleted = 0
                                        WHERE a.user_id = %d
                                        group by a.client_id
                                        union
                                        /* get all the other clients that dun have records and use settings for client_id = 1 */
                                        SELECT max(d.`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_client` a 
                                        left join `' .$wpdb->prefix .'myi_mst_user_client_role` b 
                                            on a.client_id = b.client_id
                                            and a.deleted = 0
                                            and b.deleted = 0
                                            and b.user_id = %d
                                        inner join ( select *
                                                     from `' .$wpdb->prefix .'myi_mst_user_client_role` 
                                                     where client_id = 1
                                                        and user_id = %d
                                                        and deleted = 0 ) c 
                                            on c.client_id = 1
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` d 
                                            on c.role_id = d.role_id
                                        WHERE b.role_id is null
                                        group by a.client_id 
                                    ) c 
                            on c.view_logs = 1
                               and c.client_id = a.client_id';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';
                
                foreach ($client as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }
            
            // prod
            if ( $prod === null || count($prod) == 0 || $prod[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.prod_id in ( 0';
                
                foreach ($prod as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // category
            if ( $category === null || count($category) == 0 || $category[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.cat_id in ( 0';

                foreach ($loc as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_prod_cat

        /**
        *  Get the client Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $client the list of clients. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param string $user_roles user_roles
        *  @return object/int if successful, false if fail
        */  
        public function get_client( $date_from, $date_to, $client, $user, $user_roles ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                f.client_cd,
                                a.client_name,
                                a.client_remark,
                                a.client_address,
                                a.client_address2,
                                a.client_address3';
                                            
                                
            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_client` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_client` f 
                            on a.client_id = f.client_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                            on c.view_logs = 1';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';
                
                foreach ($client as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_client

        /**
        *  Get the location Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $loc the list of location. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param string $user_roles user_roles
        *  @return object/int if successful, false if fail
        */  
        public function get_location( $date_from, $date_to, $loc, $user, $user_roles ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                d.location_cd,
                                a.`location_name`,
                                a.`location_desc`,
                                a.`location_remark`,
                                a.`add-field1`,
                                a.`add-field2`,
                                a.`add-field3`,
                                a.`add-field4`,
                                a.`add-field5`';

            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_location` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_location` d
                            on a.location_id = d.location_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                            on c.view_logs = 1';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // loc
            if ( $loc === null || count($loc) == 0 || $loc[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.location_id in ( 0';

                foreach ($loc as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;


            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_location

        /**
        *  Get the product Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $prod the list of product. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param string $user_roles user_roles
        *  @return object/int if successful, false if fail
        */  
        public function get_product( $date_from, $date_to, $prod, $user, $user_roles ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                g.prod_cd,
                                a.`prod_name`,
                                a.`prod_desc`,
                                a.`prod_dimension`,
                                a.`prod_img_url`,
                                a.`prod_remark`,
                                a.`logo_lang`,
                                a.`add-field1`,
                                a.`add-field2`,
                                a.`add-field3`,
                                a.`add-field4`,
                                a.`add-field5`';
                                            
                                
            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_product` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_product` g
                            on a.prod_id = g.prod_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                            on c.view_logs = 1';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';
                
                foreach ($client as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }
            
            // prod
            if ( $prod === null || count($prod) == 0 || $prod[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.prod_id in ( 0';
                
                foreach ($prod as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // loc
            if ( $loc === null || count($loc) == 0 || $loc[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.location_id in ( 0';

                foreach ($loc as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // client_for
            if ( $client_for === null || count($client_for) == 0 || $client_for[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id_for in ( 0';

                foreach ($client_for as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_product

        /**
        *  Get the UOM Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $uom the list of UOMs. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param string $user_roles user_roles
        *  @return object/int if successful, false if fail
        */  
        public function get_uom( $date_from, $date_to, $uom, $user, $user_roles ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                g.uom_shortname,
                                g.uom_shortname_p,
                                a.`uom_fullname`,
                                a.`uom_fullname_p`,
                                a.`uom_remark`';


            $select_body =' FROM `' .$wpdb->prefix .'myi_logm_uom` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_uom` g
                            on a.uom_id = g.uom_id
                        INNER JOIN ( SELECT max(`view_logs`) as `view_logs`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                            on c.view_logs = 1';

            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // uom           
            if ( $uom === null || count($uom) == 0 || $uom[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.uom_id in ( 0';
                
                foreach ($uom as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_uom

        /**
        *  Get the product uom Log
        *
        *  @param string $date_from if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param string $date_to must be set if $date_from is set. must be in YYYY-MM-DD HH24:MM:SS format
        *  @param array of string $client the list of clients. If empty or 1st element id is 0, will get all
        *  @param array of string $prod the list of product. If empty or 1st element id is 0, will get all
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param int @caller_user_id user_id of person running the log
        *  @return object/int if successful, false if fail
        */  
        public function get_prod_uom( $date_from, $date_to, $client, $prod, $user, $caller_user_id ) {

            global $wpdb;

            $select_col = ' SELECT  c.display_name,
                                a.create_date,
                                a.action,
                                f.client_cd,
                                g.prod_cd,
                                a.desc as uom_set_desc,
                                prod_uom,
                                a.uom_default_level';

            $select_body =' FROM `' .$wpdb->prefix .'myi_vw_log_prod_uoms` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_client` f 
                            on a.client_id = f.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_product` g
                            on a.prod_id = g.prod_id
                        INNER JOIN (    SELECT max(`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` b 
                                            on a.role_id = b.role_id
                                            and a.deleted = 0
                                        WHERE a.user_id = %d
                                        group by a.client_id
                                        union
                                        /* get all the other clients that dun have records and use settings for client_id = 1 */
                                        SELECT max(d.`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_client` a 
                                        left join `' .$wpdb->prefix .'myi_mst_user_client_role` b 
                                            on a.client_id = b.client_id
                                            and a.deleted = 0
                                            and b.deleted = 0
                                            and b.user_id = %d
                                        inner join ( select *
                                                     from `' .$wpdb->prefix .'myi_mst_user_client_role` 
                                                     where client_id = 1
                                                        and user_id = %d
                                                        and deleted = 0 ) c 
                                            on c.client_id = 1
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` d 
                                            on c.role_id = d.role_id
                                        WHERE b.role_id is null
                                        group by a.client_id ) c 
                            on c.view_logs = 1 
                               and c.client_id = a.client_id';
                        
            $where = ' WHERE ';
            
            // check for date
            if ( $date_from === null ) {
                $where .= ' if(1=1, true, a.create_date between %s and %s)';
                $date_from = '';
                $date_to = '';
            } else {
                $where .= ' a.create_date between %s and %s';
            }
            
            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';
                
                foreach ($client as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }
            
            // prod
            if ( $prod === null || count($prod) == 0 || $prod[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.prod_id in ( 0';
                
                foreach ($prod as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // user
            if ( $user === null || count($user) == 0 || $user[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.create_by_id in ( 0';
                
                foreach ($user as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // for logs group by shall be empty as logs are not supposed to do any aggregation
            $group_by = '';
            $order_by = ' ORDER BY a.create_date desc, c.display_name';
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by;

            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $date_from,
                                                                $date_to ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_prod_uom
    } // class
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Log already exists. Action aborted...'); 
} // Myi_Log
?>