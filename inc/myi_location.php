<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Location' ) ) { 
    class Myi_Location {  
        private $location_id, $location_cd, $location_name , $location_desc, $location_remark;
        private $add_field1, $add_field2, $add_field3, $add_field4, $add_field5;
        
        public function __construct() {     
            $this->location_id = null;
            $this->location_cd = null;
            $this->location_name = null;
            $this->location_desc = null;
            $this->location_remark = null;       
            $this->add_field1 = null;
            $this->add_field2 = null;
            $this->add_field3 = null;
            $this->add_field4 = null;
            $this->add_field5 = null;
        }
        
        public function get_location_id() {
            return $this->location_id;
        }
        
        public function get_location_cd() {
            return $this->location_cd;
        }

        public function get_location_name() {
            return $this->location_name;
        }

        public function get_location_desc() {
            return $this->location_desc;
        }

        public function get_location_remark() {
            return $this->location_remark;
        }

        public function get_add_field1() {
            return $this->add_field1;
        }

        public function get_add_field2() {
            return $this->add_field2;
        }

        public function get_add_field3() {
            return $this->add_field3;
        }

        public function get_add_field4() {
            return $this->add_field4;
        }

        public function get_add_field5() {
            return $this->add_field5;
        }

       /**
		* get the location list
		*
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_location_list( $user_roles = null ) {         
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_location` a 
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
                    $id .= $row->location_id .',';
                    $cd .= $row->location_cd .'~|`';
                    $name .= $row->location_name .'~|`';
                }

                $id = substr( $id, 0, -1 );
                $cd = substr( $cd, 0, -3 );
                $name = substr( $name, 0, -3 );
            
                return array( $id, $cd, $name );
            }
        } // get_location_list

       /**
		* get the location by location_id
		*
        * @param string $location_id the location_id.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_location_by_id( $location_id, $user_roles = null ) {         
            if ( $location_id === null || $location_id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_location` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`location_id` = %d 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $location_id ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->location_id = $my_result[0]->location_id;
                $this->location_cd = $my_result[0]->location_cd;
                $this->location_name = $my_result[0]->location_name;
                $this->location_desc = $my_result[0]->location_desc;
                $this->location_remark = $my_result[0]->location_remark;
                $this->add_field1 = $my_result[0]->{"add-field1"};
                $this->add_field2 = $my_result[0]->{"add-field2"};
                $this->add_field3 = $my_result[0]->{"add-field3"};
                $this->add_field4 = $my_result[0]->{"add-field4"};
                $this->add_field5 = $my_result[0]->{"add-field5"};

                return true;
            }
        } // get_location_by_id
        
       /**
		* get the location by location_cd
		*
        * @param string $location_cd the location_cd.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_location( $location_cd, $user_roles = null ) {         
            if ( $location_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_location` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`location_cd` = TRIM(%s) 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $location_cd ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->location_id = $my_result[0]->location_id;
                $this->location_cd = $my_result[0]->location_cd;
                $this->location_name = $my_result[0]->location_name;
                $this->location_desc = $my_result[0]->location_desc;
                $this->location_remark = $my_result[0]->location_remark;
                $this->add_field1 = $my_result[0]->{"add-field1"};
                $this->add_field2 = $my_result[0]->{"add-field2"};
                $this->add_field3 = $my_result[0]->{"add-field3"};
                $this->add_field4 = $my_result[0]->{"add-field4"};
                $this->add_field5 = $my_result[0]->{"add-field5"};

                return true;
            }
        } // get_location         
        
       /**
		* add in new location
		*
        * @param string $location_cd the location_cd
		* @param string $location_name The location_name
        * @param string $location_desc the location_desc
        * @param string $location_remark The location_remark     
        * @param string $add_field1 additional field 1
        * @param string $add_field2 additional field 2
        * @param string $add_field3 additional field 3
        * @param string $add_field4 additional field 4
        * @param string $add_field5 additional field 5
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function add_location( $location_cd, $location_name, $location_desc, $location_remark, $add_field1, $add_field2, $add_field3, $add_field4, $add_field5, $create_by, $user_roles = null  ) {         
            if ( $location_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_location` 
                     ( `location_cd`, `location_name`, `location_remark`, `location_desc`,
                       `add-field1`, `add-field2`, `add-field3`,
                       `add-field4`, `add-field5`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id` )
                     SELECT TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                         TRIM(%s), TRIM(%s), TRIM(%s), 
                         TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d   
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_location` 
                        ( `action`, `location_id`, `location_cd`, `location_name`, `location_remark`, `location_desc`,
                          `add-field1`, `add-field2`, `add-field3`,
                          `add-field4`, `add-field5`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';
                       
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $location_cd,
                                                               $location_name,
                                                               $location_remark,
                                                               $location_desc,
                                                               $add_field1,
                                                               $add_field2,
                                                               $add_field3,
                                                               $add_field4, 
                                                               $add_field5,
                                                               $create_by,
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $location_cd, 
                                                                   $location_name, 
                                                                   $location_remark, 
                                                                   $location_desc,
                                                                   $add_field1,
                                                                   $add_field2,
                                                                   $add_field3,
                                                                   $add_field4,
                                                                   $add_field5,
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->location_id =  $id[0]->id;
                    $this->location_cd = $location_cd;
                    $this->location_name = $location_name;
                    $this->location_desc = $location_desc;
                    $this->location_remark = $location_remark;
                    $this->add_field1 = $add_field1;
                    $this->add_field2 = $add_field2;
                    $this->add_field3 = $add_field3;
                    $this->add_field4 = $add_field4;
                    $this->add_field5 = $add_field5;

                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // add_location        
        
       /**
		* modify the location 
		*
		* @param string $location_name The location_name
        * @param string $location_desc the location_desc
        * @param string $location_remark The location_remark     
        * @param string $add_field1 additional field 1
        * @param string $add_field2 additional field 2
        * @param string $add_field3 additional field 3
        * @param string $add_field4 additional field 4
        * @param string $add_field5 additional field 5
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function mod_location( $location_name, $location_desc, $location_remark, $add_field1, $add_field2, $add_field3, $add_field4, $add_field5, $create_by, $user_roles = null ) {         
            if ( $this->location_id === null || $this->location_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
                              
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_location` a 
                     INNER JOIN ( SELECT max(`mod_inventory_master`) as `mod_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_inventory_master = 1
                     SET `location_name` = TRIM(%s),
                         `location_desc` = TRIM(%s),
                         `location_remark` = TRIM(%s),
                         `add-field1` = TRIM(%s),
                         `add-field2` = TRIM(%s),
                         `add-field3` = TRIM(%s),
                         `add-field4` = TRIM(%s),
                         `add-field5` = TRIM(%s),                         
                         `last_mod_date` = \'' .myi_getDatetimeNow() .'\',   
                         `last_mod_by_id` = %d 
                     WHERE a.`location_id` = ' .$this->location_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_location` 
                         ( `action`, `location_id`, `location_cd`, `location_name`, `location_remark`, `location_desc`,
                          `add-field1`, `add-field2`, `add-field3`,
                          `add-field4`, `add-field5`, `create_date`, `create_by_id` )
                          values
                          ( \'Update\', ' .$this->location_id .', TRIM(\'' .addslashes($this->location_cd) .'\'), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';                              
                            
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $location_name,
                                                               $location_desc,
                                                               $location_remark,
                                                               $add_field1,
                                                               $add_field2,
                                                               $add_field3,
                                                               $add_field4,
                                                               $add_field5,
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $location_name,
                                                                   $location_remark,
                                                                   $location_desc, 
                                                                   $add_field1, 
                                                                   $add_field2, 
                                                                   $add_field3, 
                                                                   $add_field4,
                                                                   $add_field5, 
                                                                   $create_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->location_name = $location_name;
                    $this->location_desc = $location_desc;
                    $this->location_remark = $location_remark;
                    $this->add_field1 = $add_field1;
                    $this->add_field2 = $add_field2;
                    $this->add_field3 = $add_field3;
                    $this->add_field4 = $add_field4;
                    $this->add_field5 = $add_field5;
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // mod_location       

       /**
		* delete the location
		*
        * @param int $delete_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients deleted and false if fail
		*/          
        public function del_location( $delete_by, $user_roles = null  ) {         
            if ( $this->location_id === null || $this->location_id == 0 ) {
               return false;
            }

            global $wpdb;        

            // ensure not in use before allow deletion
            $stmt = 'SELECT prod_uom_id 
                     FROM `' .$wpdb->prefix .'myi_txt_inventory` 
                     WHERE location_id = ' .$this->location_id .'
                     GROUP BY prod_uom_id';
                     
            $result = $wpdb->get_results( $stmt );
            for ($cnt = 0; $cnt < count($result); $cnt++ ) {
                // check that all the stock count is zero for all that use that location
                $inv = new Myi_Inventory();
                
                if ( $inv->get_stock_count_by_prod_uom_id ( $result[$cnt]->prod_uom_id, $user_roles, $this->location_id )[0] > 0 ) {
                    return false;
                }
            }

            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_location` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_location` 
                                 WHERE deleted <> 0
                                    and location_cd = TRIM(\'' .addslashes($this->location_cd) .'\')
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d 
                     WHERE a.`location_id` = ' .$this->location_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_location` 
                         ( `action`, `location_id`, `location_cd`, `create_date`, `create_by_id` )
                          values
                          ( \'Delete\', ' .$this->location_id .', TRIM(\'' .addslashes($this->location_cd) .'\'),\'' .myi_getDatetimeNow() .'\', %d );';  

                         
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
        } // del_location                    
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Location already exists. Action aborted...'); 
} // Myi_Location
?>