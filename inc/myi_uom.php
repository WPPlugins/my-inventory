<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_UOM' ) ) { 
    class Myi_UOM {  
        private $uom_id, $uom_shortname, $uom_fullname, $uom_remark, $uom_shortname_p, $uom_fullname_p;
        
        public function __construct() {     
            $this->uom_id = null;
            $this->uom_shortname = null;
            $this->uom_fullname = null;
            $this->uom_remark = null;
            $this->uom_shortname_p = null;
            $this->uom_fullname_p = null;
        }
        
        public function get_uom_shortname() {
            return $this->uom_shortname;
        }

        public function get_uom_shortname_p() {
            return $this->uom_shortname_p;
        }
        
        public function get_uom_id() {
            return $this->uom_id;
        }

        public function get_uom_fullname() {
            return $this->uom_fullname;
        }

        public function get_uom_fullname_p() {
            return $this->uom_fullname_p;
        }

        public function get_uom_remark() {
            return $this->uom_remark;
        }        
        
       /**
		* get the uoms list
		*
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_uoms_list( $user_roles = null ) {                    
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_uom` a 
                     INNER JOIN ( SELECT max(`view_inventory`) as `view_inventory`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory = 1
                     WHERE a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $stmt );

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $id = '';
                $cd = '';
                $name = '';
        
                foreach ($my_result as $row) {
                    $id .= $row->uom_id .',';
                    $cd .= $row->uom_shortname .'~|`';
                    $name .= $row->uom_fullname .'~|`';
                }

                $id = substr( $id, 0, -1 );
                $cd = substr( $cd, 0, -3 );
                $name = substr( $name, 0, -3 );
            
                return array( $id, $cd, $name );
            }
        } // get_uoms_list        

       /**
		* get the uom by id
		*
        * @param string $uom_id the uom_id.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_uom_by_id( $uom_id, $user_roles = null ) {         
            if ( $uom_id === null || $uom_id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_uom` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`uom_id` = %d 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $uom_id ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->uom_id = $my_result[0]->uom_id;
                $this->uom_shortname = $my_result[0]->uom_shortname;
                $this->uom_fullname = $my_result[0]->uom_fullname;
                $this->uom_remark = $my_result[0]->uom_remark;
                $this->uom_shortname_p = $my_result[0]->uom_shortname_p;
                $this->uom_fullname_p = $my_result[0]->uom_fullname_p;                

                return true;
            }
        } // get_uom_by_id  
        
       /**
		* get the uom by uom_shortname
		*
        * @param string $uom_shortname the uom_shortname.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_uom( $uom_shortname, $user_roles = null ) {         
            if ( $uom_shortname === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_uom` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`uom_shortname` = TRIM(%s) 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $uom_shortname ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->uom_id = $my_result[0]->uom_id;
                $this->uom_shortname = $my_result[0]->uom_shortname;
                $this->uom_fullname = $my_result[0]->uom_fullname;
                $this->uom_remark = $my_result[0]->uom_remark;
                $this->uom_shortname_p = $my_result[0]->uom_shortname_p;
                $this->uom_fullname_p = $my_result[0]->uom_fullname_p;                

                return true;
            }
        } // get_uom         
        
       /**
		* add in new uom
		*
        * @param string $uom_shortname the uom_shortname
        * @param string $uom_shortname_p the plural form of the shortname
		* @param string $uom_fullname The uom_fullname
        * @param string $uom_fullname_p The plural form of uom_fullname
        * @param string $uom_remark the uom_remark
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function add_uom( $uom_shortname, $uom_shortname_p, $uom_fullname, $uom_fullname_p, $uom_remark, $create_by, $user_roles = null  ) {         
            if ( $uom_shortname === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_uom` 
                     ( `uom_shortname`, `uom_fullname`, `uom_remark`, `uom_shortname_p`, `uom_fullname_p`,
                       `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id` )
                     SELECT TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d   
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_uom` 
                        ( `action`, `uom_id`, `uom_shortname`, `uom_fullname`, `uom_remark`, 
                          `uom_shortname_p`, `uom_fullname_p`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';
                       
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $uom_shortname, 
                                                               $uom_fullname, 
                                                               $uom_remark, 
                                                               $uom_shortname_p, 
                                                               $uom_fullname_p, 
                                                               $create_by,
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $uom_shortname, 
                                                                   $uom_fullname, 
                                                                   $uom_remark, 
                                                                   $uom_shortname_p, 
                                                                   $uom_fullname_p, 
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->uom_id =  $id[0]->id;
                    $this->uom_shortname = $uom_shortname;
                    $this->uom_fullname = $uom_fullname;
                    $this->uom_remark = $uom_remark;
                    $this->uom_shortname_p = $uom_shortname_p;
                    $this->uom_fullname_p = $uom_fullname_p;  

                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // add_uom        
        
       /**
		* modify the uom 
		*
		* @param string $uom_fullname The uom_fullname
        * @param string $uom_shortname_p the plural form of the shortname
		* @param string $uom_fullname The uom_fullname
        * @param string $uom_fullname_p The plural form of uom_fullname
        * @param string $uom_remark the uom_remark
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function mod_uom( $uom_shortname_p, $uom_fullname, $uom_fullname_p, $uom_remark, $create_by, $user_roles = null ) {         
            if ( $this->uom_id === null || $this->uom_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
                              
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_uom` a 
                     INNER JOIN ( SELECT max(`mod_inventory_master`) as `mod_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_inventory_master = 1
                     SET `uom_shortname_p` = TRIM(%s),
                         `uom_fullname` = TRIM(%s),
                         `uom_fullname_p` = TRIM(%s),
                         `uom_remark` = TRIM(%s),                     
                         `last_mod_date` = \'' .myi_getDatetimeNow() .'\',   
                         `last_mod_by_id` = %d 
                     WHERE a.`uom_id` = ' .$this->uom_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_uom` 
                         ( `action`, `uom_id`, `uom_shortname`, `uom_fullname`, `uom_remark`, 
                          `uom_shortname_p`, `uom_fullname_p`, `create_date`, `create_by_id` )
                          values
                          ( \'Update\', ' .$this->uom_id .', TRIM(\'' .addslashes($this->uom_shortname) .'\'), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';                              
                            
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $uom_shortname_p, 
                                                               $uom_fullname, 
                                                               $uom_fullname_p, 
                                                               $uom_remark, 
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $uom_fullname, 
                                                                   $uom_remark, 
                                                                   $uom_shortname_p, 
                                                                   $uom_fullname_p, 
                                                                   $create_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->uom_fullname = $uom_fullname;
                    $this->uom_remark = $uom_remark;
                    $this->uom_shortname_p = $uom_shortname_p;
                    $this->uom_fullname_p = $uom_fullname_p;  
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // mod_uom       

       /**
		* delete the uom
		*
        * @param int $delete_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients deleted and false if fail
		*/          
        public function del_uom( $delete_by, $user_roles = null  ) {         
            if ( $this->uom_id === null || $this->uom_id == 0 ) {
               return false;
            }            
           
            global $wpdb;     
            
            // ensure that it is not been in used 
            $stmt = 'SELECT id 
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` 
                     WHERE (IFNULL(`uom_level_-9_id`,0) = ' .$this->uom_id .' 
                        or IFNULL(`uom_level_-8_id`,0) = ' .$this->uom_id .' 
                        or IFNULL(`uom_level_-7_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-6_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-5_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-4_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-3_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-2_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_-1_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_0_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_1_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_2_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_3_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_4_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_5_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_6_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_7_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_8_id`,0) = ' .$this->uom_id .'
                        or IFNULL(`uom_level_9_id`,0) = ' .$this->uom_id .')
                        and deleted = 0';

            if ( count($wpdb->get_results( $stmt )) > 0 ) {
                return false;
            };                          
            
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_uom` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_uom` 
                                 WHERE deleted <> 0
                                    and uom_shortname = TRIM(\'' .addslashes($this->uom_shortname) .'\')
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d  
                     WHERE a.`uom_id` = ' .$this->uom_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_uom` 
                         ( `action`, `uom_id`, `uom_shortname`, `create_date`, `create_by_id` )
                          values
                          ( \'Delete\', ' .$this->uom_id .', TRIM(\'' .addslashes($this->uom_shortname) .'\'),\'' .myi_getDatetimeNow() .'\', %d );';  

                         
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $delete_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $delete_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // del_uom                  
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_UOM already exists. Action aborted...'); 
} // Myi_UOM
?>