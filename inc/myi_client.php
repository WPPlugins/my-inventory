<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Client' ) ) { 
    class Myi_Client {  
        private $client_id, $client_cd, $client_name, $client_remark, $client_address, $client_address2, $client_address3;        
        
        public function __construct() {     
            $this->client_id = null;
            $this->client_cd = null;
            $this->client_name = null;
            $this->client_remark = null;           
            $this->client_address = null;
            $this->client_address2 = null;
            $this->client_address3 = null;
        }
        
        public function get_client_id() {
            return $this->client_id;
        }
        
        public function get_client_cd() {
            return $this->client_cd;
        }
        
        public function get_client_name() {
            return $this->client_name;
        }    

        public function get_client_remark() {
            return $this->client_remark;
        }    

        public function get_client_address() {
            return $this->client_address;
        }       

        public function get_client_address2() {
            return $this->client_address2;
        }     

        public function get_client_address3() {
            return $this->client_address3;
        }           

       /**
		* get the client by id
		*
        * @param string $client_cd the client_cd.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_client_by_id( $client_id, $user_roles = null ) {         
            if ( $client_id === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_client` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`client_id` = %d 
                        and a.deleted = 0';

                        
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $client_id ));       
                                                              
            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->client_id = $my_result[0]->client_id;
                $this->client_cd = $my_result[0]->client_cd;
                $this->client_name = $my_result[0]->client_name;
                $this->client_remark = $my_result[0]->client_remark;           
                $this->client_address = $my_result[0]->client_address;  
                $this->client_address2 = $my_result[0]->client_address2;
                $this->client_address3 = $my_result[0]->client_address3;
                
                return true;
            }
        } // get_client_by_id           

       /**
		* get all the clients (used only for assigning users as will not check if have rights to read
        * eg first time user creation
		*
		* @return array of client_id separated by comma, client_cd separated by delimiter group ~|`, client_name separated by delimiter group ~|` and false if fail
		*/          
        public function get_all_clients() {                    
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_client` a 
                     WHERE a.deleted = 0';
                     
            $my_results =  $wpdb->get_results( $stmt );       

            if ( !$my_results ) {
                return false;
            }

            $client_list = '';
            $client_cd_list = '';
            $client_name_list = '';

            foreach ($my_results as $row) {
                $client_list .= $row->client_id .',';
                $client_cd_list .= $row->client_cd .'~|`';
                $client_name_list .= $row->client_name .'~|`';
            }

            $client_list = substr( $client_list, 0, -1 );
            $client_cd_list = substr( $client_cd_list, 0, -3 );
            $client_name_list = substr( $client_name_list, 0, -3 );

            return array( $client_list, $client_cd_list, $client_name_list );
        } // get_all_clients   
        
       /**
		* get the client 
		*
        * @param string $client_cd the client_cd.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_client( $client_cd, $user_roles = null ) {         
            if ( $client_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_client` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`client_cd` = TRIM(%s) 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $client_cd ));       
                                                              
            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->client_id = $my_result[0]->client_id;
                $this->client_cd = $my_result[0]->client_cd;
                $this->client_name = $my_result[0]->client_name;
                $this->client_remark = $my_result[0]->client_remark;           
                $this->client_address = $my_result[0]->client_address;  
                $this->client_address2 = $my_result[0]->client_address2;
                $this->client_address3 = $my_result[0]->client_address3;
                
                return true;
            }
        } // get_client         
        
       /**
		* add in new client 
		*
        * @param string $client_cd the client_cd
		* @param string $client_name The client_name
        * @param string $client_remark the client remark
        * @param string $client_address the client address number 1
        * @param string $client_address2 the client address number 2
        * @param string $client_address3 the client address number 3
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function add_client( $client_cd, $client_name, $client_remark, $client_address, $client_address2, $client_address3, $create_by, $user_roles = null  ) {         
            if ( $client_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_client` 
                     ( `client_cd`, `client_name`, `client_remark`, 
                       `client_address`, `client_address2`, 
                       `client_address3`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id` )
                     SELECT TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d  
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_client` 
                         ( `action`, `client_id`, `client_cd`, `client_name`, `client_remark`, 
                           `client_address`, `client_address2`, 
                           `client_address3`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';
            
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $client_cd,
                                                               $client_name,
                                                               $client_remark,
                                                               $client_address,
                                                               $client_address2,
                                                               $client_address3,
                                                               $create_by,
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $client_cd,
                                                                   $client_name,
                                                                   $client_remark,
                                                                   $client_address,
                                                                   $client_address2,
                                                                   $client_address3,
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {                  
                    $this->client_cd = $client_cd;
                    $this->client_name = $client_name;
                    $this->client_remark = $client_remark;           
                    $this->client_address = $client_address;
                    $this->client_address2 = $client_address2;
                    $this->client_address3 = $client_address3;
                    $this->client_id = $id[0]->id;
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // add_client        
        
       /**
		* modify the client 
		*
		* @param string $client_name The client_name
        * @param string $client_remark the client remark
        * @param string $client_address the client address number 1
        * @param string $client_address2 the client address number 2
        * @param string $client_address3 the client address number 3
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function mod_client( $client_name, $client_remark, $client_address, $client_address2, $client_address3, $create_by, $user_roles = null  ) {         
            if ( $this->client_id === null || $this->client_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
                              
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_client` a 
                     INNER JOIN ( SELECT max(`mod_inventory_master`) as `mod_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_inventory_master = 1
                     SET `client_name` = TRIM(%s),
                         `client_remark` = TRIM(%s),
                         `client_address` = TRIM(%s),
                         `client_address2` = TRIM(%s),
                         `client_address3` = TRIM(%s),
                         `last_mod_date` = \'' .myi_getDatetimeNow() .'\',   
                         `last_mod_by_id` = %d 
                     WHERE a.`client_id` = ' .$this->client_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_client` 
                         ( `action`, `client_id`, `client_cd`, `client_name`, `client_remark`, 
                           `client_address`, `client_address2`, `client_address3`, `create_date`, `create_by_id` )
                          values
                          ( \'Update\', ' .$this->client_id .', TRIM(\'' .addslashes($this->client_cd) .'\'), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  

            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $client_name,
                                                               $client_remark,
                                                               $client_address,
                                                               $client_address2,
                                                               $client_address3,
                                                               $create_by
                                                               ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $client_name,
                                                                   $client_remark,
                                                                   $client_address,
                                                                   $client_address2,
                                                                   $client_address3,
                                                                   $create_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->client_name = $client_name;
                    $this->client_remark = $client_remark;           
                    $this->client_address = $client_address;
                    $this->client_address2 = $client_address2;
                    $this->client_address3 = $client_address3;
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // mod_client       

       /**
		* delete the client 
		*
        * @param int $delete_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients deleted and false if fail
		*/          
        public function del_client( $delete_by, $user_roles = null  ) {         
            if ( $this->client_id === null || $this->client_id == 0 ) {
               return false;
            }

            // disallow deletion of client_id 1 as it is used for roles
            if ( $this->client_id == 1 ) {
               return false;
            }

            global $wpdb;       

            // ensure that it is not been in used 
            $stmt = 'SELECT client_id 
                     FROM `' .$wpdb->prefix .'myi_mst_user_client_role` 
                     WHERE client_id = ' .$this->client_id .'
                        and deleted = 0
                     UNION
                     SELECT client_id 
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` 
                     WHERE client_id = ' .$this->client_id .'
                        and deleted = 0
                     UNION
                     SELECT client_id 
                     FROM `' .$wpdb->prefix .'myi_mst_category_product` 
                     WHERE client_id = ' .$this->client_id .'
                        and deleted = 0
                     UNION
                     SELECT client_id 
                     FROM `' .$wpdb->prefix .'myi_txt_inventory` 
                     WHERE (client_id = ' .$this->client_id .'
                        or IFNULL(client_id_for,0) = ' .$this->client_id .')
                        and deleted = 0';

            if ( count($wpdb->get_results( $stmt )) > 0 ) {
                return false;
            };                         
                        
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_client` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_client` 
                                 WHERE deleted <> 0
                                    and client_cd = TRIM(\'' .addslashes($this->client_cd) .'\')
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d 
                     WHERE a.`client_id` = ' .$this->client_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_client` 
                         ( `action`, `client_id`, `client_cd`, `create_date`, `create_by_id` )
                          values
                          ( \'Delete\', ' .$this->client_id .', TRIM(\'' .addslashes($this->client_cd) .'\'),\'' .myi_getDatetimeNow() .'\', %d );';  
           
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
        } // del_client                    
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Client already exists. Action aborted...'); 
} // Myi_Client
?> 