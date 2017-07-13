<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Prod_Cat' ) ) { 
    class Myi_Prod_Cat { 
        private $id, $client_id, $cat_id, $prod_id;
        
        public function __construct() {     
            $this->id = null;
            $this->client_id = null;
            $this->cat_id = null;
            $this->prod_id = null;
        }

        
        /**
		* Get all the products in the category
		*
        * @param int $cat_id
        * @param int $client_id
        * @param string $user_roles The access rights of the user
        * @return int/bool clients inserted and false if fail
		*/          
        public function get_prod_in_cat ( $cat_id, $client_id, $user_roles = null  ) {         
            if ( $cat_id === null || $cat_id == 0 || $client_id === null || $client_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
            
            $stmt = 'SELECT a.id, d.prod_id, d.prod_cd, d.prod_name 
                     FROM `' .$wpdb->prefix .'myi_mst_category_product` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        ON c.view_inventory_master = 1
                     INNER JOIN `' .$wpdb->prefix .'myi_mst_product` d 
                        ON a.prod_id = d.prod_id
                     WHERE a.deleted = 0
                        AND a.cat_id = %d 
                        AND a.client_id = %d';

            $my_result = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                             $cat_id,
                                                             $client_id ));
                                          
            return $my_result;
        } // get_prod_in_cat   
        
        /**
		* link product to category
		*
        * @param int $prod_id
        * @param int $cat_id
        * @param int $client_id
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function link_prod_to_cat ( $prod_id, $cat_id, $client_id, $create_by, $user_roles = null  ) {         
            if ( $prod_id === null || $prod_id == 0 || $cat_id === null || $cat_id == 0 || $client_id === null || $client_id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_category_product` 
                     ( `client_id`, `cat_id`, `prod_id`, `create_date`, `create_by_id` )
                     SELECT %d,%d,%d, \'' .myi_getDatetimeNow() .'\', %d   
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_category_product` 
                         ( `action`, `id`, `client_id`, `cat_id`, `prod_id`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), %d, %d, %d, \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';


            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $client_id,
                                                               $cat_id, 
                                                               $prod_id,
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $client_id, 
                                                                   $cat_id, 
                                                                   $prod_id, 
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->id = $id[0]->id;
                    $this->client_id = $client_id;
                    $this->cat_id = $cat_id;
                    $this->prod_id = $prod_id;
                    
                    $wpdb->query('COMMIT'); // both succeed
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                }

                return $rows_inserted;                             
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK'); 
                return false;
            }                                   
        } // link_prod_to_cat   

       /**
		* unlink product to category
		*
        * @param int $id the id of the row        
        * @param int $delete_by The user who delete it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function unlink_prod_to_cat ( $id, $delete_by, $user_roles = null  ) {         
            if ( $id === null || $id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_category_product` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt 
                                  FROM `' .$wpdb->prefix .'myi_mst_category_product` aa
                                  INNER JOIN `' .$wpdb->prefix .'myi_mst_category_product` bb
                                    on aa.cat_id = bb.cat_id
                                    and aa.client_id = bb.client_id
                                    and aa.prod_id = bb.prod_id
                                  WHERE aa.deleted <> 0
                                    and bb.id = %d
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d 
                     WHERE a.`id` = %d                         
                        and a.deleted = 0';
                                                         
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_category_product` 
                         ( `action`, `id`, `client_id`, `cat_id`, `prod_id`, `create_date`, `create_by_id` )
                          SELECT \'Delete\', `id`, `client_id`, `cat_id`, `prod_id`, \'' .myi_getDatetimeNow() .'\', %d 
                          FROM `' .$wpdb->prefix .'myi_mst_category_product` a 
                          WHERE a.id = %d';

            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $id,
                                                               $delete_by,
                                                               $id ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $delete_by,
                                                                   $id ));
                
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
        } // link_prod_to_cat            
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Prod_Cat already exists. Action aborted...'); 
} // Myi_Prod_Cat
?>