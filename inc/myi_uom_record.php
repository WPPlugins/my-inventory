<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_UOM_Record' ) ) { 
    // A single record in @wp_@myi_mst_product_uom table 
    class Myi_UOM_Record {
        private $id, $client_id, $prod_id, $desc, $uom_default_level, $create_date, $create_by_id, $last_mod_date, $last_mod_by_id;
        private $client_cd, $client_name, $prod_name;
        private $uom_set; // array of arrays. Inner arrays will consists of uom_id, uom_qty_in_smallest_uom, next_uom_qty, existing_uom
        private $exists_in_db = false;
        private $modifications_made = false;
        private $last_uom_lvl = 9; // Set to last UOM level. Currently last level is level 9 
        private $first_uom_lvl = -9; 

        
        public function __construct() {
            // do initialization to default value
            $this->id = 0;
            $this->client_id = 0;
            $this->prod_id = 0;
            $this->uom_default_level = 0;
            $this->create_date = '';
            $this->create_by_id = 0;
            $this->last_mod_date = '';
            $this->last_mod_by_id = 0;
            $this->client_cd = '';
            $this->client_name = '';
            $this->prod_name = '';
            $this->uom_set = array();
            $this->exists_in_db = false;
            $this->modifications_made = false;
        }   // Constructor
        
        public function get_prod_id() {
            return $this->prod_id;
        }
        
        public function get_id() {
            return $this->id;
        }
        
        public function get_client_id() {
            return $this->client_id;
        }        
        
        public function get_desc() {
            return $this->desc;
        }
        
        public function get_client_cd() {
            return $this->client_cd;
        }

        public function get_client_name() {
            return $this->client_name;
        }

        public function get_prod_name() {
            return $this->prod_name;
        }
        
        public function get_uom_default_level() {
            return $this->uom_default_level;
        }
        
        public function get_first_uom_lvl() {
            return $this->first_uom_lvl;
        }
        
        public function get_last_uom_lvl() {
            return $this->last_uom_lvl;
        }
        
        public function set_client_and_prod_id( $cli_id, $prd_id ) {
            $this->client_id = $cli_id;
            $this->prod_id = $prd_id;
            $this->modifications_made = true;            
        }
        
        public function get_uom_nxt_lvl( $arr = null ) {
            if ( $arr === null && !( $this->id === null || $this->id == 0) ) {
                $arr = $this->uom_set;
            }
            
            $new_arr = array();
            
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {
                array_push ( $new_arr, array( $arr[$cnt][0], $arr[$cnt][2] ) );
            }
            
            return $new_arr;
        }
                
        /**
        * function to return arrays of array of uom_id, uom_qty_smallest_uom, uom_qty_next_uom
        *
        * @param array $uom_arr Array of UOM set. Each set will be another array consisting of uom_id and uom_qty_next_uom     
        * @return array of array of uom_id, uom_qty_smallest_uom, uom_qty_next_uom
        */
        public function get_uom_arr( $uom_arr ) {
            $uom_qty_smallest = array();            
            $no_of_elems = count( $uom_arr ) - 1;
            
            $smallest_uom = true;
            $cur_uom_qty_smallest = null;
            $cur_uom_qty_next = null;            
            for ( $cnt = $no_of_elems; $cnt >= 0 ; $cnt-- ) {
                if ( !is_null($uom_arr[$cnt][0]) && !$uom_arr[$cnt][0] == 0 ) { // ignore any that is null or 0
                    if ( $smallest_uom  ) { // first time in loop
                        $cur_uom_qty_smallest = 1;
                        $cur_uom_qty_next = 0;
                        $smallest_uom = false;
                    } else {
                        $cur_uom_qty_smallest *= $uom_arr[$cnt][1];
                        $cur_uom_qty_next = (int) $uom_arr[$cnt][1];
                    }
                } else {
                    $cur_uom_qty_smallest = null;
                    $cur_uom_qty_next = null;   
                }
                array_unshift($uom_qty_smallest, array( $uom_arr[$cnt][0], $cur_uom_qty_smallest, $cur_uom_qty_next));
            }
            
            return $uom_qty_smallest;
        }

        
        /**
		* function to insert the @wp_@myi_mst_product_uom table first time inserted
        * This function will try to center the UOM such as the center UOM will lie in level 0.
        * If there are 5 UOMs entered, 3rd UOM will be level 0 and respective will be level -2 to 2.
        * If there are 4 UOMs entered, 3rd UOM will be level 0 and respective will be level -2 to 1.
        * The smallest UOM will always have qty set to 1 as there shall only be 1 qty of itself.
        *
		*      
		* @param string $desc The description of that set of UOM for that production.
		* @param int $create_by The user Id of the person who create the record.
		* @param string $user_roles Determine whether to check if user role has access rights.         
		* @param int $new_uom_deflt_lvl The default UOM to use that the user select.
		* @param array $uoms Array of UOMs set. currently table only support up to 19 sets. Each set will be another array consisting of uom_id and uom_qty_next_uom      
        * @return int|bool number of records inserted and false if error
		*/
        public function initial_uom_add( $descr, $create_by, $user_roles, $uom_deflt_lvl, $uoms ) {
            global $wpdb;
            $uom_arr = $this->get_uom_arr( $uoms );            
            $no_of_uom = count( $uom_arr );
            $half_uom_no = (int) ($no_of_uom / 2);
            $cur_uom = 0; // The current UOM to populate. Currently from 0 to 18

            // check if duplicated UOM set for this client_id and prod_id.
            // $client_id or $prod_id not set will also exit without insertion
            if ( 0 == $this->client_id || 0 == $this->prod_id || $this->get_record( $this->client_id, $this->prod_id, $this->strip_empty_uom( $uoms ) ) > 0 ) {
                return false;
            }
            
            // calculate the $new_uom_deflt_lvl. top level always start at -9 atm
            $first_uom_pos = $half_uom_no * -1; 

            $new_uom_deflt_lvl = $first_uom_pos - ( $this->first_uom_lvl - $uom_deflt_lvl ) ;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_product_uom` (`client_id`, `prod_id`, `desc`,
                                                              `uom_default_level`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id`,
                                                              `uom_level_-9_id`, `l-9_qty`, `l-9_qty_nxt_lvl`,
                                                              `uom_level_-8_id`, `l-8_qty`, `l-8_qty_nxt_lvl`,
                                                              `uom_level_-7_id`, `l-7_qty`, `l-7_qty_nxt_lvl`,
                                                              `uom_level_-6_id`, `l-6_qty`, `l-6_qty_nxt_lvl`,
                                                              `uom_level_-5_id`, `l-5_qty`, `l-5_qty_nxt_lvl`,
                                                              `uom_level_-4_id`, `l-4_qty`, `l-4_qty_nxt_lvl`,
                                                              `uom_level_-3_id`, `l-3_qty`, `l-3_qty_nxt_lvl`,
                                                              `uom_level_-2_id`, `l-2_qty`, `l-2_qty_nxt_lvl`,
                                                              `uom_level_-1_id`, `l-1_qty`, `l-1_qty_nxt_lvl`,
                                                              `uom_level_0_id`, `l0_qty`, `l0_qty_nxt_lvl`,
                                                              `uom_level_1_id`, `l1_qty`, `l1_qty_nxt_lvl`,
                                                              `uom_level_2_id`, `l2_qty`, `l2_qty_nxt_lvl`,
                                                              `uom_level_3_id`, `l3_qty`, `l3_qty_nxt_lvl`,
                                                              `uom_level_4_id`, `l4_qty`, `l4_qty_nxt_lvl`,
                                                              `uom_level_5_id`, `l5_qty`, `l5_qty_nxt_lvl`,
                                                              `uom_level_6_id`, `l6_qty`, `l6_qty_nxt_lvl`,
                                                              `uom_level_7_id`, `l7_qty`, `l7_qty_nxt_lvl`,
                                                              `uom_level_8_id`, `l8_qty`, `l8_qty_nxt_lvl`,
                                                              `uom_level_9_id`, `l9_qty`, `l9_qty_nxt_lvl` ) 
                        SELECT ' .$this->client_id .', ' .$this->prod_id .', TRIM(%s), 
                                  %d, \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d ';
                                  
            for ( $cur_lvl = $this->first_uom_lvl; $cur_lvl <= $this->last_uom_lvl; $cur_lvl++ ) {
                if ( $cur_uom < $no_of_uom && abs( $cur_lvl ) <= $half_uom_no ) { // $cur_uom < $no_of_uom will ensure correct population for even number of UOM
                    $stmt = $stmt .',' .$uom_arr[$cur_uom][0] .', ' .$uom_arr[$cur_uom][1] .', ' .$uom_arr[$cur_uom][2] .' ';
                    $cur_uom++;
                } else {
                    $stmt = $stmt .',NULL ,NULL, NULL ';
                }
            } // for
            
            $stmt = $stmt .'
                        FROM ( 
                                SELECT max(create_inventory_master) as create_inventory_master
                                FROM ' .$wpdb->prefix .'myi_mst_roles 
                                WHERE role_id in (\'\',' .$user_roles .')
                              ) a
                        WHERE a.create_inventory_master = 1;';
            
            try {
                // begin transaction
                $wpdb->query('START TRANSACTION');
                // $uoms is not in prepare statement as it is dynamically populated. However, there shalln't be any chance of SQL Injection as
                // SQL query will fail if number of columns inserted is different from that specified
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $descr,
                                                               $new_uom_deflt_lvl, 
                                                               $create_by,
                                                               $create_by ));
                
                $stmt2 = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product_uom`
                          (`action`, `id`, `client_id`, `prod_id`, `desc`,
                           `uom_default_level`, `create_date`, `create_by_id`, 
                           `uom_level_-9_id`, `l-9_qty_nxt_lvl`,
                           `uom_level_-8_id`, `l-8_qty_nxt_lvl`,
                           `uom_level_-7_id`, `l-7_qty_nxt_lvl`,
                           `uom_level_-6_id`, `l-6_qty_nxt_lvl`,
                           `uom_level_-5_id`, `l-5_qty_nxt_lvl`,
                           `uom_level_-4_id`, `l-4_qty_nxt_lvl`,
                           `uom_level_-3_id`, `l-3_qty_nxt_lvl`,
                           `uom_level_-2_id`, `l-2_qty_nxt_lvl`,
                           `uom_level_-1_id`, `l-1_qty_nxt_lvl`,
                           `uom_level_0_id`, `l0_qty_nxt_lvl`,
                           `uom_level_1_id`, `l1_qty_nxt_lvl`,
                           `uom_level_2_id`, `l2_qty_nxt_lvl`,
                           `uom_level_3_id`, `l3_qty_nxt_lvl`,
                           `uom_level_4_id`, `l4_qty_nxt_lvl`,
                           `uom_level_5_id`, `l5_qty_nxt_lvl`,
                           `uom_level_6_id`, `l6_qty_nxt_lvl`,
                           `uom_level_7_id`, `l7_qty_nxt_lvl`,
                           `uom_level_8_id`, `l8_qty_nxt_lvl`,
                           `uom_level_9_id`, `l9_qty_nxt_lvl` ) 
                           SELECT \'Create\', LAST_INSERT_ID(), `client_id`, `prod_id`, `desc`,
                                  `uom_default_level`, `create_date`, `create_by_id`, 
                                  `uom_level_-9_id`, `l-9_qty_nxt_lvl`,
                                  `uom_level_-8_id`, `l-8_qty_nxt_lvl`,
                                  `uom_level_-7_id`, `l-7_qty_nxt_lvl`,
                                  `uom_level_-6_id`, `l-6_qty_nxt_lvl`,
                                  `uom_level_-5_id`, `l-5_qty_nxt_lvl`,
                                  `uom_level_-4_id`, `l-4_qty_nxt_lvl`,
                                  `uom_level_-3_id`, `l-3_qty_nxt_lvl`,
                                  `uom_level_-2_id`, `l-2_qty_nxt_lvl`,
                                  `uom_level_-1_id`, `l-1_qty_nxt_lvl`,
                                  `uom_level_0_id`, `l0_qty_nxt_lvl`,
                                  `uom_level_1_id`, `l1_qty_nxt_lvl`,
                                  `uom_level_2_id`, `l2_qty_nxt_lvl`,
                                  `uom_level_3_id`, `l3_qty_nxt_lvl`,
                                  `uom_level_4_id`, `l4_qty_nxt_lvl`,
                                  `uom_level_5_id`, `l5_qty_nxt_lvl`,
                                  `uom_level_6_id`, `l6_qty_nxt_lvl`,
                                  `uom_level_7_id`, `l7_qty_nxt_lvl`,
                                  `uom_level_8_id`, `l8_qty_nxt_lvl`,
                                  `uom_level_9_id`, `l9_qty_nxt_lvl` 
                           FROM `' .$wpdb->prefix .'myi_mst_product_uom`
                           WHERE `id` = LAST_INSERT_ID();';
                           
                $rows_inserted_log = $wpdb->query( $stmt2 );
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                if ( $rows_inserted > 0 ) {
                    $this->modifications_made = true;
                    $this->exists_in_db = true;
                }
                return $rows_inserted;                
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }
        } //initial_uom_add
        
        /**
		* function to copy the UOM sets for another client
        * This function will try to center the UOM such as the center UOM will lie in level 0 even though the original UOM set is not centralize. (Eg add in new UOM after initial creation of UOM)
        *      
        * @param int $prod_uom_id the prod_uom_id to copy from
        * @param int $client_id_to the client_id to copy to
		* @param string $desc The description of that set of UOM for that production for to
		* @param int $create_by The user Id of the person who create the record.
		* @param string $user_roles Determine whether to check if user role has access rights.         
		* @param int $new_uom_deflt_lvl The default UOM to use that the user select for to.
        * @return int|bool number of records inserted and false if error
		*/
        public function copy_prod_uom( $prod_uom_id, $client_id_to, $descr, $create_by, $user_roles, $new_uom_deflt_lvl ) {
            global $wpdb;
            
            $my_prod_uom = new Myi_UOM_Record();
            $my_prod_uom->get_record_by_id( $prod_uom_id );
                    
            $this->set_client_and_prod_id( $client_id_to, $my_prod_uom->get_prod_id() );
            
            $first_used_uom = -999;
            $rows = $my_prod_uom->get_uom_nxt_lvl();

            //get first used uom level
            for ( $cnt = $my_prod_uom->get_first_uom_lvl(); $cnt <= $my_prod_uom->get_last_uom_lvl(); $cnt++ ) {
                    if (!(  $rows[$cnt + $my_prod_uom->get_last_uom_lvl()][0] === null || 
                            $rows[$cnt + $my_prod_uom->get_last_uom_lvl()][0] == 0) && $first_used_uom === -999) {
                        $first_used_uom = $cnt;
                        break;
                    }
            }

            // find the default uom level with first used uom as level -9
            $new_uom_deflt_lvl = $my_prod_uom->get_first_uom_lvl() - ( $first_used_uom - $new_uom_deflt_lvl );

            // strip all empty uom so that will centralize the UOM
            return( $this->initial_uom_add( $descr, $create_by, $user_roles, $new_uom_deflt_lvl, $my_prod_uom->strip_empty_uom( $rows ) ) );
        } //copy_prod_uom        
        
        // $uom first element must be id, 2nd element must be next_level_qty
        public function strip_empty_uom ( $uom = null ) {
            // set default if no uom passed in, use $this->uom_set
            if ( $uom === null && !( $this->id === null || $this->id == 0) ) {
                $uom = $this->uom_set;
            }
            
            $new_arr = array();
            
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++) {
                if ( $uom[$cnt][0] != null && $uom[$cnt][0] != 0 ) {
                    array_push( $new_arr, $uom[$cnt] );
                }
            }
            
            // set last qty to be always 0 as no lower level
            if ( count($new_arr) > 0 ) {
                $new_arr[ count($new_arr) - 1 ][1] = 0;
            }
            return $new_arr;
        }
        
        /**
		* function to add in new uom into the @wp_@myi_mst_product_uom table 
        * This function will build on the initial uom.
        * Thus in theory, the program supports from 10-19 levels of UOM
        * before calling this function, must call get_record or get_record_by_id to ensure no modifications_made
        *  
		* @param string $desc The description of that set of UOM for that production.
		* @param int $mod_by The user Id of the person who create the record.
        * @param string $user_roles Determine whether to check if user role has access rights. 
		* @param int $new_uom_deflt_lvl The default UOM to use that the user select.
		* @param array $p_uoms Array of UOMs set. currently table only support up to 19 sets. Each set will be another array consisting of uom_id and uom_qty_next_uom    
        * @return int/bool number of records updated. false if fail
		*/
        public function update_uom( $descr, $mod_by, $user_roles, $new_uom_deflt_lvl, $p_uoms ) {     
            $uoms = $p_uoms;
            $strip_uom = $this->strip_empty_uom( $uoms );          

            // if records for this $uoms already exists in database, don't update. 
            if ( $this->id == 0 || count( $uoms ) != $this->last_uom_lvl - $this->first_uom_lvl + 1 || $this->modifications_made || 
                 $this->get_record( $this->client_id, $this->prod_id, $strip_uom, null, null, $this->id ) > 0 ) {
                return false;
            }
           
            global $wpdb;      
             
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {
                // prevent any updating of existing_uom except for the smallest uom in database which will allow uom_qty_next_uom to be changed from 0.
                if ( $this->uom_set[$cnt][3] === true ) { // existing_uom
                    $uoms[$cnt][0] = (int) $this->uom_set[$cnt][0]; // reset the uom_id
                    if ( $this->uom_set[$cnt][2] > 0 ) {
                        $uoms[$cnt][1] = (int) $this->uom_set[$cnt][2]; // reset the uom_qty_next_uom                        
                    }
                }
            }
          
            $uom_arr = $this->get_uom_arr( $uoms );    
                        
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_product_uom` a
                            INNER JOIN ( SELECT MAX(mod_inventory_master) as mod_inventory_master
                                         FROM `' .$wpdb->prefix .'myi_mst_roles` 
                                         WHERE role_id in (\'\',' .$user_roles .') ) b
                                ON b.mod_inventory_master = 1
                            SET `desc` = %s,
                                `uom_default_level` = %d, 
                                `last_mod_date` = \'' .myi_getDatetimeNow() .'\', 
                                `last_mod_by_id` = %d ';
                                
                                
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {              
                $stmt = $stmt .',`uom_level_' .(int) ( $this->first_uom_lvl + $cnt ) .'_id` = ' 
                                 .( $uom_arr[$cnt][0] == 0 || is_null($uom_arr[$cnt][0]) ? 'NULL' : $uom_arr[$cnt][0] ).',
                                 `l' .(int) ( $this->first_uom_lvl + $cnt ) .'_qty` = ' 
                                 .( $uom_arr[$cnt][0] == 0 || is_null($uom_arr[$cnt][1]) ? 'NULL' : $uom_arr[$cnt][1] ).',
                                 `l' .(int) ( $this->first_uom_lvl + $cnt ) .'_qty_nxt_lvl` = ' 
                                 .( $uom_arr[$cnt][0] == 0 || is_null($uom_arr[$cnt][2]) ? 'NULL' : $uom_arr[$cnt][2] );    
            }                                
            $stmt = $stmt .' WHERE a.id = ' .$this->id .';';    

            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product_uom`
                         (`action`, `id`, `client_id`, `prod_id`, `desc`,  
                          `uom_default_level`, `create_date`, `create_by_id`,
                          `uom_level_-9_id`, `l-9_qty_nxt_lvl`, 
                          `uom_level_-8_id`, `l-8_qty_nxt_lvl`,  
                          `uom_level_-7_id`, `l-7_qty_nxt_lvl`,
                          `uom_level_-6_id`, `l-6_qty_nxt_lvl`,
                          `uom_level_-5_id`, `l-5_qty_nxt_lvl`,                          
                          `uom_level_-4_id`, `l-4_qty_nxt_lvl`,
                          `uom_level_-3_id`, `l-3_qty_nxt_lvl`,
                          `uom_level_-2_id`, `l-2_qty_nxt_lvl`,
                          `uom_level_-1_id`, `l-1_qty_nxt_lvl`,
                          `uom_level_0_id`, `l0_qty_nxt_lvl`,
                          `uom_level_1_id`, `l1_qty_nxt_lvl`,
                          `uom_level_2_id`, `l2_qty_nxt_lvl`,
                          `uom_level_3_id`, `l3_qty_nxt_lvl`,
                          `uom_level_4_id`, `l4_qty_nxt_lvl`,
                          `uom_level_5_id`, `l5_qty_nxt_lvl`,
                          `uom_level_6_id`, `l6_qty_nxt_lvl`,
                          `uom_level_7_id`, `l7_qty_nxt_lvl`,
                          `uom_level_8_id`, `l8_qty_nxt_lvl`,
                          `uom_level_9_id`, `l9_qty_nxt_lvl`)
                        SELECT \'Update\', ' .$this->id .',' .$this->client_id .',' .$this->prod_id .',%s,
                            %d,\'' .myi_getDatetimeNow() .'\',%d';
                        
            for ( $cnt = 0; $cnt <= $this->last_uom_lvl - $this->first_uom_lvl; $cnt++ ) {  
                $log_stmt = $log_stmt .',' .( is_null($p_uoms[$cnt][0]) ? 'NULL' : $p_uoms[$cnt][0] ) 
                                      .',' .( is_null($p_uoms[$cnt][1]) ? 'NULL' : $p_uoms[$cnt][1] );                                       
            }

            try {
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $descr, 
                                                               $new_uom_deflt_lvl, 
                                                               $mod_by ));
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $descr, 
                                                                   $new_uom_deflt_lvl, 
                                                                   $mod_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                if ( $rows_inserted > 0 ) {
                    $this->modifications_made = true;
                    $this->exists_in_db = true;
                }
                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                         
        } //update_uom
        
        /**
		* Delete prod_uom_id
        * return the number of records deleted
        *
		*
        * @param int $del_by the user id of the person who delete the record
		* @param string $user_roles Determine whether to check if user role has access rights. 
        * @return int/bool count of records deleted and false if fail
		*/
        public function del_prod_uom( $del_by, $user_roles ) {
            global $wpdb;
            $inv = new Myi_Inventory();
          
            // ensure that id, prod_id and client_id are been populated and record not modified
            if ( $this->id === NULL || $this->id == 0 || $this->client_id === NULL || $this->client_id == 0 || $this->prod_id === NULL || $this->prod_id == 0 || $this->modifications_made ) {
                return false;
            }

            
            // only allow deletion if inventory count is zero
            if ( $inv->get_stock_count_by_prod_uom_id( $this->id, $user_roles )[0] != 0 ) {
                return false;
            }
                                    
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_product_uom` a
                     inner join ( SELECT max(delete_inventory_master) as delete_inventory_master
                                  FROM ' .$wpdb->prefix .'myi_mst_roles 
                                  WHERE role_id in (\'\',' .$user_roles .') 
                                 ) d
                         on d.delete_inventory_master = 1
                     SET a.deleted = 1,
                         a.delete_by_id = %d,
                         a.delete_date = \'' .myi_getDatetimeNow() .'\' 
                     WHERE a.id = ' .$this->id  
                            .' and a.deleted = 0'; 
                            
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product_uom`
                         (`action`, `id`, `client_id`, `prod_id`, `create_date`, `create_by_id`)
                         VALUES 
                         (\'Delete\', ' .$this->id .',' .$this->client_id .',' .$this->prod_id .',\'' .myi_getDatetimeNow() .'\', %d);';


            try {
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $del_by ));
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $del_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                if ( $rows_inserted > 0 ) {
                    $this->modifications_made = true;
                    $this->exists_in_db = false;
                }
                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                         
        } //del_prod_uom       

        /**
        # return the records
        *
		*
		* @param int $cli_id The client Id.
		* @param int $prd_id The product Id.
        * @param string $user_roles Determine whether to check if user role has access rights. If empty, means no need to check access rights. (Eg. when checking for duplicate records before insertion)
        * @param string $rights_to_check the access rights to check for. If not specified, will be checking view_inventory_master
        * @return object the query return object
		*/
        public function get_record_by_cli_prd( $cli_id, $prd_id, $user_roles = null , $rights_to_check = null ) {
            global $wpdb;

            if ( $user_roles === NULL ) {
                $user_roles = 'myi_none';
            }
            
            if ( $rights_to_check === null ) {
                $rights_to_check = 'view_inventory_master';
            }

            $stmt = 'SELECT a.*, b.client_cd, b.client_name, c.prod_name,
                     `l-9`.uom_shortname as `l-9_shortname`,`l-9`.uom_shortname_p as `l-9_shortname_p`,
                     `l-8`.uom_shortname as `l-8_shortname`,`l-8`.uom_shortname_p as `l-8_shortname_p`,
                     `l-7`.uom_shortname as `l-7_shortname`,`l-7`.uom_shortname_p as `l-7_shortname_p`,
                     `l-6`.uom_shortname as `l-6_shortname`,`l-6`.uom_shortname_p as `l-6_shortname_p`,
                     `l-5`.uom_shortname as `l-5_shortname`,`l-5`.uom_shortname_p as `l-5_shortname_p`,
                     `l-4`.uom_shortname as `l-4_shortname`,`l-4`.uom_shortname_p as `l-4_shortname_p`,
                     `l-3`.uom_shortname as `l-3_shortname`,`l-3`.uom_shortname_p as `l-3_shortname_p`,
                     `l-2`.uom_shortname as `l-2_shortname`,`l-2`.uom_shortname_p as `l-2_shortname_p`,
                     `l-1`.uom_shortname as `l-1_shortname`,`l-1`.uom_shortname_p as `l-1_shortname_p`,
                     `l0`.uom_shortname as `l0_shortname`,`l0`.uom_shortname_p as `l0_shortname_p`,
                     `l1`.uom_shortname as `l1_shortname`,`l1`.uom_shortname_p as `l1_shortname_p`,
                     `l2`.uom_shortname as `l2_shortname`,`l2`.uom_shortname_p as `l2_shortname_p`,
                     `l3`.uom_shortname as `l3_shortname`,`l3`.uom_shortname_p as `l3_shortname_p`,
                     `l4`.uom_shortname as `l4_shortname`,`l4`.uom_shortname_p as `l4_shortname_p`,
                     `l5`.uom_shortname as `l5_shortname`,`l5`.uom_shortname_p as `l5_shortname_p`,
                     `l6`.uom_shortname as `l6_shortname`,`l6`.uom_shortname_p as `l6_shortname_p`,
                     `l7`.uom_shortname as `l7_shortname`,`l7`.uom_shortname_p as `l7_shortname_p`,
                     `l8`.uom_shortname as `l8_shortname`,`l8`.uom_shortname_p as `l8_shortname_p`,
                     `l9`.uom_shortname as `l9_shortname`,`l9`.uom_shortname_p as `l9_shortname_p`
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` a
                     inner join `' .$wpdb->prefix .'myi_mst_client` b
                        on a.client_id = b.client_id
                        /*and b.deleted = 0*/
                     inner join `' .$wpdb->prefix .'myi_mst_product` c
                        on a.prod_id = c.prod_id
                        /*and c.deleted = 0*/';

            if ( $user_roles != 'myi_none' ) {
                $stmt = $stmt . '
                     inner join ( SELECT max(' .$rights_to_check .') as view_inventory_master
                                  FROM ' .$wpdb->prefix .'myi_mst_roles 
                                  WHERE role_id in (\'\',' .$user_roles .') 
                                 ) d
                         on d.view_inventory_master = 1
                    ';
            }

            $stmt = $stmt .'
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-9`
                        on `l-9`.uom_id = a.`uom_level_-9_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-8`
                        on `l-8`.uom_id = a.`uom_level_-8_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-7`
                        on `l-7`.uom_id = a.`uom_level_-7_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-6`
                        on `l-6`.uom_id = a.`uom_level_-6_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-5`
                        on `l-5`.uom_id = a.`uom_level_-5_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-4`
                        on `l-4`.uom_id = a.`uom_level_-4_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-3`
                        on `l-3`.uom_id = a.`uom_level_-3_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-2`
                        on `l-2`.uom_id = a.`uom_level_-2_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l-1`
                        on `l-1`.uom_id = a.`uom_level_-1_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l0`
                        on `l0`.uom_id = a.`uom_level_0_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l1`
                        on `l1`.uom_id = a.`uom_level_1_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l2`
                        on `l2`.uom_id = a.`uom_level_2_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l3`
                        on `l3`.uom_id = a.`uom_level_3_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l4`
                        on `l4`.uom_id = a.`uom_level_4_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l5`
                        on `l5`.uom_id = a.`uom_level_5_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l6`
                        on `l6`.uom_id = a.`uom_level_6_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l7`
                        on `l7`.uom_id = a.`uom_level_7_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l8`
                        on `l8`.uom_id = a.`uom_level_8_id`
                     LEFT JOIN `' .$wpdb->prefix .'myi_mst_uom` `l9`
                        on `l9`.uom_id = a.`uom_level_9_id`
                     WHERE a.client_id = %d AND a.prod_id = %d 
                        and a.deleted = 0'; 

            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $cli_id,
                                                              $prd_id )); 

            return $my_results;
        } //get_record_by_cli_prd          
        
        /**
		* function to ensure that no duplicate records for same client_id, prod_id and uom_id in order.
        * Note that the function have to take into consideration cases when initially user create UOM - PCS (level 0).
        *                                                                                Then add UOM - Box (Level -1) on top of PCS.
        *                                                                             Add another UOM - CTN (Level -2) on top of CTN.
        # return the number of records with such records
        *
        * When tried to create another new UOM with same client_id, prod_id, and uom_id (CTN, Box, PCS) even though their level is different,
        * this function will pick up as duplicate. (Eg PCS (level 1), Box (Level 0), CTN (Level 1)
		*
		* @param int $cli_id The client Id.
		* @param int $prd_id The product Id.        
		* @param array $uom_arr Array of UOMs setted. currently table only support up to 19 sets. Each set will be another array consisting of at least uom_id, uom_qty
        * @param string $user_roles Determine whether to check if user role has access rights. If empty, means no need to check access rights. (Eg. when checking for duplicate records before insertion)
        * @param boolean $populate_class whether to populate the variables of this class. Default is no.
        * @param int $ignore_id The id to ignore (eg checking that record don't already exists when update)
        * @return int count of records retrieved
		*/
        public function get_record( $cli_id, $prd_id, $uom_arr, $user_roles = null, $populate_class = null, $ignore_id = null) {
            global $wpdb;
            $no_of_uom = count( $uom_arr );
            $concat_uom_id = ',0,';
            $concat_uom_qty = ',0,';
            
            if ( $populate_class === NULL ) {
                $populate_class = 0;
            }
            
            if ( $ignore_id === null ) {
                // set to -999 so that condition <> $ignore will always be true
                $ignore_id = -999;
            }
                     
            // if $uom_arr is empty, return 0 records found
            if ( $no_of_uom == 0 ) {
                return 0;
            }

            $uom_arr = $this->strip_empty_uom( $uom_arr );

            for ( $cnt = 0; $cnt < $no_of_uom; $cnt++) {
                $concat_uom_id = $concat_uom_id .(int) $uom_arr[$cnt][0] .',';
                $concat_uom_qty .= (int) $uom_arr[$cnt][1] .',';
            }

            $concat_uom_id = $concat_uom_id .'0,';
            $concat_uom_qty .= '0,';

            $stmt = 'SELECT a.*, b.client_cd, b.client_name, c.prod_name 
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` a
                     inner join `' .$wpdb->prefix .'myi_mst_client` b
                        on a.client_id = b.client_id
                        /*and b.deleted = 0*/
                     inner join `' .$wpdb->prefix .'myi_mst_product` c
                        on a.prod_id = c.prod_id
                        /*and c.deleted = 0*/';
                        
            if ( $user_roles !== null ) {
                $stmt = $stmt . '
                     inner join ( SELECT max(view_inventory_master) as view_inventory_master
                                  FROM ' .$wpdb->prefix .'myi_mst_roles 
                                  WHERE role_id in (\'\',' .$user_roles .') 
                                 ) d
                         on d.view_inventory_master = 1
                    ';
            }
                        
            $stmt = $stmt .'
                     WHERE a.client_id = %d AND a.prod_id = %d 
                      AND INSTR(
                        CONCAT(\',0,\', IFNULL(`uom_level_-9_id`,0),\',\',IFNULL(`uom_level_-8_id`,0),\',\',IFNULL(`uom_level_-7_id`,0),\',\',IFNULL(`uom_level_-6_id`,0),\',\',IFNULL(`uom_level_-5_id`,0),\',\',
                        IFNULL(`uom_level_-4_id`,0),\',\',IFNULL(`uom_level_-3_id`,0),\',\',IFNULL(`uom_level_-2_id`,0),\',\',IFNULL(`uom_level_-1_id`,0),\',\',IFNULL(`uom_level_0_id`,0),\',\',
                        IFNULL(`uom_level_1_id`,0),\',\',IFNULL(`uom_level_2_id`,0),\',\',IFNULL(`uom_level_3_id`,0),\',\',IFNULL(`uom_level_4_id`,0),\',\',IFNULL(`uom_level_5_id`,0),\',\',
                        IFNULL(`uom_level_6_id`,0),\',\',IFNULL(`uom_level_7_id`,0),\',\',IFNULL(`uom_level_8_id`,0),\',\',IFNULL(`uom_level_9_id`,0),\',0,\')'
                        .',\'' .$concat_uom_id .'\') > 0 
                      AND INSTR(
                        CONCAT(\',0,\', IFNULL(`l-9_qty_nxt_lvl`,0),\',\',IFNULL(`l-8_qty_nxt_lvl`,0),\',\',IFNULL(`l-7_qty_nxt_lvl`,0),\',\',IFNULL(`l-6_qty_nxt_lvl`,0),\',\',IFNULL(`l-5_qty_nxt_lvl`,0),\',\',
                        IFNULL(`l-4_qty_nxt_lvl`,0),\',\',IFNULL(`l-3_qty_nxt_lvl`,0),\',\',IFNULL(`l-2_qty_nxt_lvl`,0),\',\',IFNULL(`l-1_qty_nxt_lvl`,0),\',\',IFNULL(`l0_qty_nxt_lvl`,0),\',\',
                        IFNULL(`l1_qty_nxt_lvl`,0),\',\',IFNULL(`l2_qty_nxt_lvl`,0),\',\',IFNULL(`l3_qty_nxt_lvl`,0),\',\',IFNULL(`l4_qty_nxt_lvl`,0),\',\',IFNULL(`l5_qty_nxt_lvl`,0),\',\',
                        IFNULL(`l6_qty_nxt_lvl`,0),\',\',IFNULL(`l7_qty_nxt_lvl`,0),\',\',IFNULL(`l8_qty_nxt_lvl`,0),\',\',IFNULL(`l9_qty_nxt_lvl`,0),\',0,\')'
                        .',\'' .$concat_uom_qty .'\') > 0 
                        and a.deleted = 0
                        and a.id <> %d'; // This line is for the INSTR

            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $cli_id,
                                                              $prd_id,
                                                              $ignore_id )); 
                              
            if ( $populate_class ) {
                // If everything working properly, will only have at most 1 record... If it happens to have more than 1, will randomly choose 1.
                $this->populate_class( $my_results );            
            } 
                  
            return count( $my_results );
        } //get_record              
    
    
        /**
		* get the record based on id
        * return the number of records with such records
        *
		*
		* @param int $id The prod_uom record Id.
		* @param string $user_roles Determine whether to check if user role has access rights. If empty, means no need to check access rights. (Eg. when checking for duplicate records before insertion)
        * @param boolean $populate_class whether to populate the variables of this class. Default is yes.
        * @return int count of records retrieved
		*/
        public function get_record_by_id( $id, $user_roles = null, $populate_class = null ) {
            global $wpdb;
            
            if ( $user_roles === NULL ) {
                $user_roles = 'myi_none';
            }
            
            if ( $populate_class === NULL ) {
                $populate_class = 1;
            }

            $stmt = 'SELECT a.*, b.client_cd, b.client_name, c.prod_name 
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` a
                     inner join `' .$wpdb->prefix .'myi_mst_client` b
                        on a.client_id = b.client_id
                        /*and b.deleted = 0*/
                     inner join `' .$wpdb->prefix .'myi_mst_product` c
                        on a.prod_id = c.prod_id
                        /*and c.deleted = 0*/';
                        
            if ( $user_roles != 'myi_none' ) {
                $stmt = $stmt . '
                     inner join ( SELECT max(view_inventory_master) as view_inventory_master
                                  FROM ' .$wpdb->prefix .'myi_mst_roles 
                                  WHERE role_id in (\'\',' .$user_roles .') 
                                 ) d
                         on d.view_inventory_master = 1
                    ';
            }
                        
            $stmt = $stmt .'
                     WHERE a.id = %d and a.deleted = 0'; 
                                                                                                           
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $id )); 
              
            if ( $populate_class ) {
                // If everything working properly, will only have at most 1 record... If it happens to have more than 1, will randomly choose 1.
                $this->populate_class( $my_results );
            } 
                  
            return count( $my_results );
        } //get_record_by_id       

        // convert the record to array of uom_id, uom_qty_in_smallest_uom, next_uom_qty, existing_uom
        public function get_uoms_in_arr( $rec ) {
                $arr = array();
                
                for ( $cnt = $this->first_uom_lvl; $cnt <= $this->last_uom_lvl; $cnt++) {             
                    eval('array_push($arr, array($rec->{"uom_level_' .(int) $cnt .'_id"}, $rec->{"l' .(int) $cnt .'_qty"}, 
                         $rec->{"l' .(int) $cnt .'_qty_nxt_lvl"},( is_null($rec->{"uom_level_' .(int) $cnt .'_id"})? false : true) ));');
                }      

                return $arr;
        }
        
        // convert the record to array of uom_id, uom_qty_in_smallest_uom, next_uom_qty, existing_uom, uom_shortname, uom_shortname_p
        public function get_uoms_in_arr_with_name( $rec ) {
                $arr = array();
                
                for ( $cnt = $this->first_uom_lvl; $cnt <= $this->last_uom_lvl; $cnt++) {             
                    eval('array_push($arr, array($rec->{"uom_level_' .(int) $cnt .'_id"}, $rec->{"l' .(int) $cnt .'_qty"}, 
                         $rec->{"l' .(int) $cnt .'_qty_nxt_lvl"},( is_null($rec->{"uom_level_' .(int) $cnt .'_id"})? false : true),
                         $rec->{"l' .(int) $cnt .'_shortname"},$rec->{"l' .(int) $cnt .'_shortname_p"}));');                         
                }      

                return $arr;
        }
        
        protected function populate_class( $my_results ) {
                $this->client_id = $my_results[0]->client_id;
                $this->prod_id = $my_results[0]->prod_id;
                $this->desc =  $my_results[0]->desc;
                $this->uom_default_level = $my_results[0]->uom_default_level;
                $this->create_date = $my_results[0]->create_date;
                $this->create_by_id = $my_results[0]->create_by_id;
                $this->last_mod_date = $my_results[0]->last_mod_date;
                $this->last_mod_by_id = $my_results[0]->last_mod_by_id;
                $this->client_cd = $my_results[0]->client_cd;
                $this->client_name = $my_results[0]->client_name;
                $this->prod_name = $my_results[0]->prod_name;
                $this->exists_in_db = true;
                $this->modifications_made = false;
                $this->id = $my_results[0]->id;
                $this->uom_set = $this->get_uoms_in_arr( $my_results[0] ); 
        } //populate_class
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_UOM_Record already exists. Action aborted...'); 
} // Myi_UOM_Record
?>