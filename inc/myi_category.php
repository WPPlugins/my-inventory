<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Category' ) ) { 
    class Myi_Category {  
        private $cat_id, $cat_cd, $cat_name, $cat_img_url;
                
        public function __construct() {     
            $this->cat_id = null;
            $this->cat_cd = null;
            $this->cat_name = null;
            $this->cat_img_url = null;
        }
        
        public function get_cat_id() {
            return $this->cat_id;
        }

        public function get_cat_cd() {
            return $this->cat_cd;
        }

        public function get_cat_name() {
            return $this->cat_name;
        }

        public function get_cat_img_url() {
            return $this->cat_img_url;
        }
        
       /**
		* get the category list
		*
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_categories_list( $user_roles = null ) {                    
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_category` a 
                     INNER JOIN ( SELECT max(`view_inventory`) as `view_inventory`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\', '.$user_roles .')) c 
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
                    $id .= $row->cat_id .',';
                    $cd .= $row->cat_cd .'~|`';
                    $name .= $row->cat_name .'~|`';
                }

                $id = substr( $id, 0, -1 );
                $cd = substr( $cd, 0, -3 );
                $name = substr( $name, 0, -3 );
            
                return array( $id, $cd, $name );
            }
        } // get_categories_list

       /**
		* get the category by id
		*
        * @param string $cat_id the cat_id.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_category_by_id( $cat_id, $user_roles = null ) {         
            if ( $cat_id === null || $cat_id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_category` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\', '.$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`cat_id` = %d 
                        and a.deleted = 0';

            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $cat_id
                                                              ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->cat_id = $my_result[0]->cat_id;
                $this->cat_cd = $my_result[0]->cat_cd;
                $this->cat_img_url = $my_result[0]->cat_img_url;
                $this->cat_name = $my_result[0]->cat_name;

                return true;
            }
        } // get_category_by_id    
        
       /**
		* get the category by cat
		*
        * @param string $cat_cd the cat_cd.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_category( $cat_cd, $user_roles = null ) {         
            if ( $cat_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_category` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\', '.$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`cat_cd` = TRIM(%s) 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $cat_cd
                                                              ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->cat_id = $my_result[0]->cat_id;
                $this->cat_cd = $my_result[0]->cat_cd;
                $this->cat_img_url = $my_result[0]->cat_img_url;
                $this->cat_name = $my_result[0]->cat_name;

                return true;
            }
        } // get_category         
        
       /**
		* add in new category
		*
        * @param string $cat_cd the cat_cd.
        * @param string $cat_name the cat_name
        * @param string $cat_img_url The cat_img_url
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function add_category( $cat_cd, $cat_name, $cat_img_url, $create_by, $user_roles = null  ) {         
            if ( $cat_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_category` 
                     ( `cat_cd`, `cat_name`, `cat_img_url`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id` )
                     SELECT TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d  
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\','.$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_category` 
                         ( `action`, `cat_id`, `cat_cd`, `cat_name`, `cat_img_url`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), TRIM(%s), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';


            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $cat_cd,
                                                               $cat_name,
                                                               $cat_img_url,
                                                               $create_by,
                                                               $create_by
                                                               ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $cat_cd,
                                                                   $cat_name,
                                                                   $cat_img_url,
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->cat_id = $id[0]->id;
                    $this->cat_cd = $cat_cd;
                    $this->cat_name = $cat_name;
                    $this->cat_img_url = $cat_img_url;                  

                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // add_category        
        
       /**
		* modify the category 
		*
        * @param string $cat_name the cat_name
        * @param string $cat_img_url The cat_img_url
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function mod_category( $cat_name, $cat_img_url, $create_by, $user_roles = null ) {         
            if ( $this->cat_id === null || $this->cat_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
                              
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_category` a 
                     INNER JOIN ( SELECT max(`mod_inventory_master`) as `mod_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\','.$user_roles .')) c 
                        on c.mod_inventory_master = 1
                     SET `cat_name` = TRIM(%s),
                         `cat_img_url` = TRIM(%s),                         
                         `last_mod_date` = \'' .myi_getDatetimeNow() .'\',   
                         `last_mod_by_id` = %d 
                     WHERE a.`cat_id` = ' .$this->cat_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_category` 
                         ( `action`, `cat_id`, `cat_cd`, `cat_name`, `cat_img_url`, `create_date`, `create_by_id` )
                          values
                          ( \'Update\', ' .$this->cat_id .', TRIM(\'' .addslashes($this->cat_cd) .'\'), TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  


            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,                                                               
                                                               $cat_name,
                                                               $cat_img_url,
                                                               $create_by));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $cat_name, 
                                                                   $cat_img_url,
                                                                   $create_by));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->cat_name = $cat_name;
                    $this->cat_img_url = $cat_img_url;   
                    
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // mod_category       

       /**
		* delete the category 
		*
        * @param int $delete_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients deleted and false if fail
		*/          
        public function del_category( $delete_by, $user_roles = null  ) {         
            if ( $this->cat_id === null || $this->cat_id == 0 ) {
               return false;
            }            
           
            global $wpdb;        

            // ensure that it is not in use before deleting
            $stmt = 'SELECT cat_id 
                     FROM `' .$wpdb->prefix .'myi_mst_category_product` 
                     WHERE deleted = 0 
                        and cat_id = ' .$this->cat_id;
                        
            if ( count($wpdb->get_results( $stmt )) > 0 ) {
                return false;
            }; 
                            
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_category` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\','.$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_category` 
                                 WHERE deleted <> 0
                                    and cat_cd = TRIM(\'' .addslashes($this->cat_cd) .'\')
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d 
                     WHERE a.`cat_id` = ' .$this->cat_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_category` 
                         ( `action`, `cat_id`, `cat_cd`, `create_date`, `create_by_id` )
                          values
                          ( \'Delete\', ' .$this->cat_id .', TRIM(\'' .addslashes($this->cat_cd) .'\'),\'' .myi_getDatetimeNow() .'\', %d );';  


            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                                                       
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $delete_by
                                                               ));    
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
        } // del_category      
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Category already exists. Action aborted...'); 
} // Myi_Category
?> 