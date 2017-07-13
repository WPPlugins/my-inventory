<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Inventory' ) ) { 
    // The inventory for a certain client_id, prod_id and prod_uom_id
    class Myi_Inventory {              
        private $last_uom_lvl = 9; // Set to last UOM level. Currently last level is level 9 
        private $first_uom_lvl = -9;     
    
        public function __construct() {
        }
        
        /**
		* get the stock count 
		*
        * @param int $prod_uom_id the prod_uom_id
		* @param string $user_roles Determine whether to check if user role has access rights. if $user_roles is null, will not check
        * @param int $location_id the location_id. If not specified, will get all location_id
        * @param int $client_id the client_id. If not specified, will get all client_id
        * @return array/bool array of inventory count (smallest uom) and smallest uom id and smallest uom shortname and smallest uom fullname 
        *    and false if fail or no access rights
		*/        
        public function get_stock_count_by_prod_uom_id ( $prod_uom_id, $user_roles = null, $location_id = null, $client_id = null ) {
            if ( is_null($prod_uom_id) || $prod_uom_id == 0 ) {
                return false;
            }

            global $wpdb;
            
            $stmt = 'SELECT     a.prod_uom_id,
                                sum(smallest_uom_qty) as smallest_uom_qty,
                                min(smallest_uom_id) as smallest_uom_id, 
                                if(sum(smallest_uom_qty) > 1, min(smallest_uom_short_p), min(smallest_uom_short)) as smallest_uom_disp_short,
                                if(sum(smallest_uom_qty) > 1, min(smallest_uom_full_p), min(smallest_uom_full)) as smallest_uom_disp_full
                     FROM `' .$wpdb->prefix .'myi_vw_inv_uoms` a ';
            
            if ( $user_roles !== null ) {
                $stmt .= ' INNER JOIN ( SELECT max(`view_inventory`) as `view_inventory`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                           on c.view_inventory = 1';
            }

            $stmt .= ' WHERE a.prod_uom_id = %d';
                     
            if ( !is_null($location_id) ) {
                $stmt = $stmt .' and a.location_id = %d' ;
            } else {
                $location_id = 0;
                $stmt .= ' and if(1=1, true, a.location_id = %d )';
            }
            
            if ( !is_null($client_id) ) {
                $stmt = $stmt .' and a.client_id = %d';
            } else {
                $client_id = 0;
                $stmt .= ' and if(1=1, true, a.client_id = %d )';
            }
            
            $stmt .= ' GROUP BY a.prod_uom_id';
                
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $prod_uom_id,
                                                              $location_id,
                                                              $client_id ));  
                                                              
            if ( count( $my_results ) == 0 ) {
                return false;
            }
            
            return array(   $my_results[0]->smallest_uom_qty, $my_results[0]->smallest_uom_id, 
                            $my_results[0]->smallest_uom_disp_short, $my_results[0]->smallest_uom_disp_full );
        }  // get_stock_count_by_prod_uom_id 
        
        /**
		* get the quantity in smallest uom by converting the $uoms
		*
        * @param int $prod_uom_id the prod_uom_id
        * @param array $uoms an array of array consisting of uom_id, uom_qty
        * @return array/bool array of inventory count (smallest uom) and smallest uom id and false if fail 
		*/        
        public function get_smallest_uom_qty ( $prod_uom_id, $uoms ) {
            if ( is_null($prod_uom_id) || $prod_uom_id == 0 ) {
                return false;
            }

            // if $uoms don't consists of same number of supported uom levels            
            if ( $uoms === null || count($uoms) != $this->last_uom_lvl - $this->first_uom_lvl + 1 ) {
                return false;
            }
            
            global $wpdb;
            
            // below is not calling the view myi_vw_inv_uoms as we are not interested in the uom wordings and thus don't want to link to 
            // too many unused tables
            $stmt = 'SELECT ';
            
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {
                $stmt = $stmt .'IFNULL(b.`l' .(int) ($cnt + $this->first_uom_lvl) .'_qty`,0) * ' .( is_null($uoms[$cnt][0]) ? 0 : $uoms[$cnt][1] ) .' +';
            }
            
            $stmt = $stmt .'0 as qty_smallest_uom,
                            COALESCE(b.`uom_level_9_id`,b.`uom_level_8_id`,b.`uom_level_7_id`,b.`uom_level_6_id`,b.`uom_level_5_id`,
                                     b.`uom_level_4_id`,b.`uom_level_3_id`,b.`uom_level_2_id`,b.`uom_level_1_id`,b.`uom_level_0_id`,
                                     b.`uom_level_-1_id`,b.`uom_level_-2_id`,b.`uom_level_-3_id`,b.`uom_level_-4_id`,b.`uom_level_-5_id`,
                                     b.`uom_level_-6_id`,b.`uom_level_-7_id`,b.`uom_level_-8_id`,b.`uom_level_-9_id`) as smallest_uom_id
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` b                    
                     WHERE b.deleted = 0
                        and b.id = %d';
                                        
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $prod_uom_id ));  
                                                              
            if ( count( $my_results ) == 0 ) {
                return false;
            }
            
            return array( $my_results[0]->qty_smallest_uom, $my_results[0]->smallest_uom_id );     
        }  // get_smallest_uom_qty         

        /**
		* get the stock count and display in respective UOM  
		*
        * @param int $prod_uom_id the prod_uom_id
        * @param int $qty the quantity in the smallest UOM
        * @param bool $display_shortname to display the shortname instead of fullname. default true
        * @param int $highest_uom_level_to_display the highest UOM to display. If not specified, will get the maximum UOM level which is -9 at the moment
        * @return array/bool array of array of quantity and uom_name and uom_id and uom_lvl. false if fail
		*/        
        public function get_stock_count_for_display (  $prod_uom_id, $qty, $display_shortname = null, $highest_uom_level_to_display = null ) {
            if ( is_null($prod_uom_id) || $prod_uom_id == 0 ) {
                return false;
            }
            
            if ( $display_shortname === null ) {
                $display_shortname = true;
            }
                        
            global $wpdb;            
            
            if ( $display_shortname ) {
                $column_to_use = 'uom_shortname';
            } else {
                $column_to_use = 'uom_fullname';
            }
            
            $highest_uom_level = $highest_uom_level_to_display;
            
            if ( $highest_uom_level_to_display === null ) {
                $highest_uom_level = $this->first_uom_lvl;
            }

            $stmt = 'SELECT 1 as dummy';
            
            for ( $cnt = $this->first_uom_lvl; $cnt <= $this->last_uom_lvl; $cnt++ ) {
                $stmt = $stmt .', a.`uom_level_' .(int) $cnt .'_id`, a.`l' .(int) $cnt .'_qty`,
                        IF(TRIM(b' .(int) ($cnt + 9) .'.`' .$column_to_use .'`) = \'\', b' .(int) ($cnt + 9) .'.`uom_shortname`, b' .(int) ($cnt + 9) .'.`' .$column_to_use .'`) as `l' .(int) $cnt .'uom`,
                        IF(TRIM(b' .(int) ($cnt + 9) .'.`' .$column_to_use .'_p`) = \'\', b' .(int) ($cnt + 9) .'.`uom_shortname_p`, b' .(int) ($cnt + 9) .'.`' .$column_to_use .'_p`) as `l' .(int) $cnt .'uom_p` ';
            }    

            $stmt = $stmt .' FROM `' .$wpdb->prefix .'myi_mst_product_uom` a ';
            
            for ( $cnt = $this->first_uom_lvl; $cnt <= $this->last_uom_lvl; $cnt++ ) {
                $stmt = $stmt .'INNER JOIN ( SELECT z.uom_id, z.uom_shortname, z.uom_shortname_p, z.uom_fullname, z.uom_fullname_p
                                             FROM `' .$wpdb->prefix .'myi_mst_uom` z
                                             union 
                                             SELECT 0, NULL, NULL, NULL, NULL
                                             FROM dual
                                            ) b' .(int) ( $cnt + 9 )
                              .'   on b' .(int) ( $cnt + 9 ) .'.uom_id = IFNULL(a.`uom_level_' . $cnt .'_id`,0) ';
            }
                     
            $stmt = $stmt .'WHERE a.deleted = 0
                                  and a.id = %d';
          
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $prod_uom_id ));  

            if ( !$my_results ) {
                return false;
            }
            
            $display_uom = array();
                        
            for ( $cnt = $this->first_uom_lvl; $cnt <= $this->last_uom_lvl; $cnt++ ) {
                eval('$level_uom_id = $my_results[0]->{"uom_level_' .$cnt .'_id"};');
                if ( $cnt >= $highest_uom_level && $level_uom_id != 0 ) {
                    eval('$level_qty = $my_results[0]->{"l' .$cnt .'_qty"};');
                    
                    $uom_qty = (int) ( $qty / $level_qty);
                    $qty = $qty - $uom_qty * $level_qty;

                    if ($uom_qty > 1) { // use plural form
                        eval('$level_uom_name = $my_results[0]->{"l' .$cnt .'uom_p"};');    
                    } else {
                        eval('$level_uom_name = $my_results[0]->{"l' .$cnt .'uom"};');
                    }

                    array_push( $display_uom, array( $uom_qty, $level_uom_name, $level_uom_id, $cnt ) );
                }
            }

            return $display_uom;
        }  // get_stock_count_for_display                

        /**
		* Add stocks (for remove stock, pass in negative values
		*
        * @param int $prod_uom_id the prod_uom_id
        * @param int $location_id location_id
        * @param int $client_id_for the product is meant for producing products for this client_id        
        * @param int $user_id the user id 
		* @param string $user_roles Determine whether to check if user role has access rights. 
        * @param string $job_no job_no
        * @param string $remarks remarks
        * @param string @field1 field1
        * @param string @field2 field2
        * @param string @field3 field3
        * @param string @field4 field4
        * @param string @field5 field5
        * @param array $uoms an array of array consisting of uom_id, uom_qty. Must be 19 rows 
        * @param bool $start_transaction whether to start transaction - first time called must start transaction otherwise will fail. 
        *             Used mainly for transfer stock as both transfer in and out must succeed in order to commit all changes. Default is start transaction
        * @param bool $dun_commit_changes whether to commit changes at end. Default is commit changes
        *             Used mainly for transfer stock as both transfer in and out must succeed in order to commit all changes
        *
        * @return number of rows inserted and false if fail
		*/        
        public function add_stock_by_prod_uom_id ( $prod_uom_id, $location_id, $client_id_for, $user_id, $user_roles, $job_no, $remarks, 
                                                   $field1, $field2, $field3, $field4, $field5, 
                                                   $uoms, $start_transaction = null, $dun_commit_changes = null ) {
            if ( is_null($prod_uom_id) || $prod_uom_id == 0 || $user_id === null || $user_id == 0 ) {
                return false;
            }
            
            // if $uoms don't consists of same number of supported uom levels
            if ( $uoms === null || count($uoms) != $this->last_uom_lvl - $this->first_uom_lvl + 1 ) {
                return false;
            }
            
            if ($start_transaction === null) {
                $start_transaction = true;
            }

            if ($dun_commit_changes === null) {
                $dun_commit_changes = false;
            }
            
            global $wpdb;
            
            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_txt_inventory` 
                     ( `prod_uom_id`, `client_id_for`, `job_no`, `remarks`, `add-field1`, `add-field2`, 
                       `add-field3`, `add-field4`, `add-field5`, `create_date`, `create_by_id`, `location_id`,
                       `qty-l-9`, `qty-l-8`, `qty-l-7`, `qty-l-6`, `qty-l-5`,
                       `qty-l-4`, `qty-l-3`, `qty-l-2`, `qty-l-1`, `qty-l0`,
                       `qty-l1`, `qty-l2`, `qty-l3`, `qty-l4`, `qty-l5`, 
                       `qty-l6`, `qty-l7`, `qty-l8`, `qty-l9` )
                     SELECT %d,' .(is_null($client_id_for) || $client_id_for == 0 ? 'IF(1=1,NULL,%d)' : '%d') .',%s,%s,%s,%s,
                            %s,%s,%s,\'' .myi_getDatetimeNow() .'\',%d, ' .(is_null($location_id) || $location_id == 0 ? 'IF(1=1,NULL,%d)' : '%d') .' ';
            
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {
                $stmt = $stmt .',' .( is_null($uoms[$cnt][0]) || $uoms[$cnt][0] == 0 || trim($uoms[$cnt][1]) == '' ? 'NULL' : $uoms[$cnt][1] );
            }
            
            $stmt = $stmt .'
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` b 
                     INNER JOIN ( SELECT max(`stock_mod_inventory`) as `stock_mod_inventory`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.stock_mod_inventory = 1
                     WHERE b.id = %d
                        and b.deleted = 0;';

            // begin transaction
            if ($start_transaction) {
                $wpdb->query('START TRANSACTION');
            }
         
            $my_results = $wpdb->query( $wpdb->prepare( $stmt,
                                                        $prod_uom_id,
                                                        $client_id_for,
                                                        $job_no,
                                                        $remarks,
                                                        $field1,
                                                        $field2,
                                                        $field3,
                                                        $field4,
                                                        $field5,
                                                        $user_id,
                                                        $location_id,
                                                        $prod_uom_id ));
          
            // do a inventory stock count and if more stock than available deleted, revert the changes
            $stock_cnt = $this->get_stock_count_by_prod_uom_id( $prod_uom_id, $user_roles );                   
            
            if ( $stock_cnt === false || $stock_cnt[0] < 0 ) {
                $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                return false;
            } else if ( !$dun_commit_changes ) {
                    $wpdb->query('COMMIT'); 
            }
                        
            return $my_results;   
        }    // add_stock_by_prod_uom_id       
        
        /**
		* Transfer stocks (for remove stock, pass in negative values
        * condition for transferring - must be same prod_id and same smallest uom_id
		*
        * @param int $prod_uom_id_from the prod_uom_id to transfer from
        * @param int $prod_uom_id_to the prod_uom_id to transfer to
        * @param int $location_id_from location_id from
        * @param int $location_id_to location_id to
        * @param int $client_id_for_from the product is meant for producing products for this client_id        
        * @param int $client_id_for_to the product is meant for producing products for this client_id        
        * @param int $user_id the user id 
		* @param string $user_roles_from Determine whether to check if user role has access rights for from
        * @param string $user_roles_to Determine whether to check if user role has access rights for to
        * @param string $job_no_from job_no for from
        * @param string $remarks_from remarks for from
        * @param string @field1_from field1 for from
        * @param string @field2_from field2 for from
        * @param string @field3_from field3 for from
        * @param string @field4_from field4 for from
        * @param string @field5_from field5 for from
        * @param string $job_no_to job_no for to
        * @param string $remarks_to remarks for to
        * @param string @field1_to field1 for to
        * @param string @field2_to field2 for to
        * @param string @field3_to field3 for to
        * @param string @field4_to field4 for to
        * @param string @field5_to field5 for to        
        * @param array $uoms_from an array of array consisting of uom_id, uom_qty (must be negative)
        * @return number of rows inserted and false if fail
		*/        
        public function transfer_stock( $prod_uom_id_from, $prod_uom_id_to, $location_id_from, $location_id_to, $client_id_for_from, $client_id_for_to, $user_id, $user_roles_from, $user_roles_to, $job_no_from, $remarks_from, 
                                                   $field1_from, $field2_from, $field3_from, $field4_from, $field5_from,
                                                   $job_no_to, $remarks_to, 
                                                   $field1_to, $field2_to, $field3_to, $field4_to, $field5_to,
                                                   $uoms_from ) {
            if ( is_null($prod_uom_id_from) || $prod_uom_id_from == 0 || is_null($prod_uom_id_to) || $prod_uom_id_to == 0 || $user_id === null || $user_id == 0 ) {
                return false;
            }
            
            // if $uoms don't consists of same number of supported uom levels
            if ( $uoms_from === null || count($uoms_from) != $this->last_uom_lvl - $this->first_uom_lvl + 1 ) {
                return false;
            }

            global $wpdb;
            
            // check that both to and from have the same smallest uom and same prod_id
            $stmt = 'SELECT 1 as allowed
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` a 
                     INNER JOIN `' .$wpdb->prefix .'myi_mst_product_uom` b 
                        on a.prod_id = b.prod_id
                        and b.id = %d
                        and b.deleted = 0
                     WHERE a.id = %d 
                        and a.deleted = 0
                        and COALESCE(b.`uom_level_9_id`,b.`uom_level_8_id`,b.`uom_level_7_id`,b.`uom_level_6_id`,b.`uom_level_5_id`,
                                     b.`uom_level_4_id`,b.`uom_level_3_id`,b.`uom_level_2_id`,b.`uom_level_1_id`,b.`uom_level_0_id`,
                                     b.`uom_level_-1_id`,b.`uom_level_-2_id`,b.`uom_level_-3_id`,b.`uom_level_-4_id`,b.`uom_level_-5_id`,
                                     b.`uom_level_-6_id`,b.`uom_level_-7_id`,b.`uom_level_-8_id`,b.`uom_level_-9_id`) =
                            COALESCE(a.`uom_level_9_id`,a.`uom_level_8_id`,a.`uom_level_7_id`,a.`uom_level_6_id`,a.`uom_level_5_id`,
                                     a.`uom_level_4_id`,a.`uom_level_3_id`,a.`uom_level_2_id`,a.`uom_level_1_id`,a.`uom_level_0_id`,
                                     a.`uom_level_-1_id`,a.`uom_level_-2_id`,a.`uom_level_-3_id`,a.`uom_level_-4_id`,a.`uom_level_-5_id`,
                                     a.`uom_level_-6_id`,a.`uom_level_-7_id`,a.`uom_level_-8_id`,a.`uom_level_-9_id`);';
                                                                       
            
            if ( $wpdb->query( $wpdb->prepare( $stmt,
                                               $prod_uom_id_to,
                                               $prod_uom_id_from )) == 0 ) {
                return false;
            }
                                     
            // transfer out the stock from 
            $result_from = $this->add_stock_by_prod_uom_id ( $prod_uom_id_from, $location_id_from, $client_id_for_from, $user_id, $user_roles_from, $job_no_from, $remarks_from, 
                                                             $field1_from, $field2_from, $field3_from, $field4_from, $field5_from,
                                                             $uoms_from, true, true ); // start transaction and don't commit changes

            if ( !$result_from ) {
                $wpdb->query('ROLLBACK'); 
                return false;                
            }
                                                             
            // transfer out qty in smallest uom
            $transfer_from_smallest_uom = $this->get_smallest_uom_qty ( $prod_uom_id_from, $uoms_from );
            
            if ( $transfer_from_smallest_uom === false ) {
                $wpdb->query('ROLLBACK'); 
                return false;                 
            }          
                     
            $transfer_to_uom_rec = new Myi_UOM_Record();
            $transfer_to_uom_rec->get_record_by_id( $prod_uom_id_to );
            $arr = $transfer_to_uom_rec->get_uom_nxt_lvl();
 
            $smallest_uom_found = false;
            
            for ( $cnt = count( $arr ) - 1; $cnt >= 0; $cnt-- ) {
                // smallest uom found, set it to quantity of transfer from but inverse sign (+/-)
                if ( !$smallest_uom_found && $arr[$cnt][0] == $transfer_from_smallest_uom[1] ) {
                    $arr[$cnt][1] = ((double) $transfer_from_smallest_uom[0]) * -1;
                    $smallest_uom_found = true;
                } else {
                    $arr[$cnt][1] = 0;
                }
            }         
            
            // transfer in the stock to
            $result_to = $this->add_stock_by_prod_uom_id ( $prod_uom_id_to, $location_id_to, $client_id_for_to, $user_id, $user_roles_to, $job_no_to, $remarks_to, 
                                                           $field1_to, $field2_to, $field3_to, $field4_to, $field5_to,
                                                           $arr, false, true ); // don't start transaction and don't commit changes   

 
            // both succeed
            if ( $result_to ) {
                $wpdb->query('COMMIT'); 
                return $result_from;
            } else {
                $wpdb->query('ROLLBACK'); 
                return false;
            }
        }    // transfer_stock               
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Inventory already exists. Action aborted...'); 
} // Myi_Inventory
?> 