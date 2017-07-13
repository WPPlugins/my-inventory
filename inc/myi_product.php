<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Product' ) ) { 
    class Myi_Product {  
        private $prod_id, $prod_cd, $prod_name, $prod_desc, $prod_dimension, $prod_img_url, $prod_remark, $logo_lang;
        private $add_field1, $add_field2, $add_field3, $add_field4, $add_field5;
        
        public function __construct() {     
            $this->prod_id = null;
            $this->prod_cd = null;
            $this->prod_name = null;
            $this->prod_desc = null;
            $this->prod_dimension = null;
            $this->prod_img_url = null;
            $this->prod_remark = null;
            $this->logo_lang = null;
            $this->add_field1 = null;
            $this->add_field2 = null;
            $this->add_field3 = null;
            $this->add_field4 = null;
            $this->add_field5 = null;
        }
        
        public function get_prod_id() {
            return $this->prod_id;
        }
        
        public function get_prod_cd() {
            return $this->prod_cd;
        }
        
        public function get_prod_name() {
            return $this->prod_name;
        }
        
        public function get_prod_desc() {
            return $this->prod_desc;
        }
        
        public function get_prod_dimension() {
            return $this->prod_dimension;
        }
        
        public function get_prod_img_url() {
            return $this->prod_img_url;
        }
        
        public function get_prod_remark() {
            return $this->prod_remark;
        }
        
        public function get_logo_lang() {
            return $this->logo_lang;
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
		* get the list of products 
		*
        * @param string $user_roles The access rights of the user who create
		* @return array of id and cd and name false if fail
		*/          
        public function get_product_list( $user_roles = null ) {                    
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_product` a 
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
                    $id .= $row->prod_id .',';
                    $cd .= $row->prod_cd .'~|`';
                    $name .= $row->prod_name .'~|`';
                }

                $id = substr( $id, 0, -1 );
                $cd = substr( $cd, 0, -3 );
                $name = substr( $name, 0, -3 );
            
                return array( $id, $cd, $name );
            }
        } // get_product_list
        
       /**
		* get the product by prod_id
		*
        * @param string $prod_id the prod_id.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_product_by_id( $prod_id, $user_roles = null ) {         
            if ( $prod_id === null || $prod_id == 0 ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_product` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`prod_id` = %d 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $prod_id ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->prod_id =  $my_result[0]->prod_id;
                $this->prod_cd =  $my_result[0]->prod_cd;
                $this->prod_name =  $my_result[0]->prod_name;
                $this->prod_desc =  $my_result[0]->prod_desc;
                $this->prod_dimension =  $my_result[0]->prod_dimension;
                $this->prod_img_url =  $my_result[0]->prod_img_url;
                $this->prod_remark =  $my_result[0]->prod_remark;
                $this->logo_lang =  $my_result[0]->logo_lang;
                $this->add_field1 = $my_result[0]->{"add-field1"};
                $this->add_field2 = $my_result[0]->{"add-field2"};
                $this->add_field3 = $my_result[0]->{"add-field3"};
                $this->add_field4 = $my_result[0]->{"add-field4"};
                $this->add_field5 = $my_result[0]->{"add-field5"};
                
                return true;
            }
        } // get_product_by_id         
        
       /**
		* get the product by prod_cd
		*
        * @param string $prod_cd the prod_cd.
        * @param string $user_roles The access rights of the user who create
		* @return bool false if fail
		*/          
        public function get_product( $prod_cd, $user_roles = null ) {         
            if ( $prod_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'myi_mst_product` a 
                     INNER JOIN ( SELECT max(`view_inventory_master`) as `view_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.view_inventory_master = 1
                     WHERE a.`prod_cd` = TRIM(%s) 
                        and a.deleted = 0';
                     
            $my_result =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $prod_cd ));       

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                $this->prod_id =  $my_result[0]->prod_id;
                $this->prod_cd =  $my_result[0]->prod_cd;
                $this->prod_name =  $my_result[0]->prod_name;
                $this->prod_desc =  $my_result[0]->prod_desc;
                $this->prod_dimension =  $my_result[0]->prod_dimension;
                $this->prod_img_url =  $my_result[0]->prod_img_url;
                $this->prod_remark =  $my_result[0]->prod_remark;
                $this->logo_lang =  $my_result[0]->logo_lang;
                $this->add_field1 = $my_result[0]->{"add-field1"};
                $this->add_field2 = $my_result[0]->{"add-field2"};
                $this->add_field3 = $my_result[0]->{"add-field3"};
                $this->add_field4 = $my_result[0]->{"add-field4"};
                $this->add_field5 = $my_result[0]->{"add-field5"};
                
                return true;
            }
        } // get_product         
        
       /**
		* add in new product
		*
        * @param string $prod_cd the prod_cd
		* @param string $prod_name The prod_name
        * @param string $prod_desc The prod description
        * @param string $prod_dimension The prod dimension
        * @param string $prod_img_url The prod image URL
        * @param string $prod_remark the prod remark
        * @param string $logo_lang the language of the logo
        * @param string $add_field1 additional field 1
        * @param string $add_field2 additional field 2
        * @param string $add_field3 additional field 3
        * @param string $add_field4 additional field 4
        * @param string $add_field5 additional field 5
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function add_product( $prod_cd, $prod_name, $prod_desc, $prod_dimension, $prod_img_url, $prod_remark, $logo_lang, $add_field1, $add_field2, $add_field3, $add_field4, $add_field5, $create_by, $user_roles = null  ) {         
            if ( $prod_cd === null ) {
               return false;
            }            
           
            global $wpdb;

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_product` 
                     ( `prod_cd`, `prod_name`, `prod_desc`, `prod_dimension`, `prod_img_url`, 
                       `prod_remark`, `logo_lang`, `add-field1`, `add-field2`, `add-field3`,
                       `add-field4`, `add-field5`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id` )
                     SELECT TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d, \'' .myi_getDatetimeNow() .'\', %d  
                     FROM  ( SELECT max(`create_inventory_master`) as `create_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                     WHERE c.create_inventory_master = 1';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product` 
                         ( `action`, `prod_id`, `prod_cd`, `prod_name`, `prod_desc`, `prod_dimension`, `prod_img_url`, 
                           `prod_remark`, `logo_lang`, `add-field1`, `add-field2`, `add-field3`,
                           `add-field4`, `add-field5`, `create_date`, `create_by_id` )
                          values
                          ( \'Create\', LAST_INSERT_ID(), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  
                  
            $id_stmt = 'SELECT LAST_INSERT_ID() as id FROM dual;';
           
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $prod_cd, 
                                                               $prod_name, 
                                                               $prod_desc, 
                                                               $prod_dimension, 
                                                               $prod_img_url, 
                                                               $prod_remark, 
                                                               $logo_lang, 
                                                               $add_field1, 
                                                               $add_field2, 
                                                               $add_field3, 
                                                               $add_field4, 
                                                               $add_field5, 
                                                               $create_by, 
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $prod_cd, 
                                                                   $prod_name, 
                                                                   $prod_desc, 
                                                                   $prod_dimension, 
                                                                   $prod_img_url, 
                                                                   $prod_remark, 
                                                                   $logo_lang, 
                                                                   $add_field1, 
                                                                   $add_field2, 
                                                                   $add_field3, 
                                                                   $add_field4,
                                                                   $add_field5, 
                                                                   $create_by ));
                $id =  $wpdb->get_results( $id_stmt );  
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->prod_id =  $id[0]->id;
                    $this->prod_cd =  $prod_cd;
                    $this->prod_name =  $prod_name;
                    $this->prod_desc =  $prod_desc;
                    $this->prod_dimension =  $prod_dimension;
                    $this->prod_img_url =  $prod_img_url;
                    $this->prod_remark =  $prod_remark;
                    $this->logo_lang =  $logo_lang;
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
        } // add_product        
        
       /**
		* modify the product 
		*
		* @param string $prod_name The prod_name
        * @param string $prod_desc The prod description
        * @param string $prod_dimension The prod dimension
        * @param string $prod_img_url The prod image URL
        * @param string $prod_remark the prod remark
        * @param string $logo_lang the language of the logo
        * @param string $add_field1 additional field 1
        * @param string $add_field2 additional field 2
        * @param string $add_field3 additional field 3
        * @param string $add_field4 additional field 4
        * @param string $add_field5 additional field 5
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients inserted and false if fail
		*/          
        public function mod_product( $prod_name, $prod_desc, $prod_dimension, $prod_img_url, $prod_remark, $logo_lang, $add_field1, $add_field2, $add_field3, $add_field4, $add_field5, $create_by, $user_roles = null ) {         
            if ( $this->prod_id === null || $this->prod_id == 0 ) {
               return false;
            }            
           
            global $wpdb;
                              
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_product` a 
                     INNER JOIN ( SELECT max(`mod_inventory_master`) as `mod_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_inventory_master = 1
                     SET `prod_name` = TRIM(%s),
                         `prod_desc` = TRIM(%s),
                         `prod_dimension` = TRIM(%s),
                         `prod_img_url` = TRIM(%s),
                         `prod_remark` = TRIM(%s),
                         `logo_lang` = TRIM(%s),
                         `add-field1` = TRIM(%s),
                         `add-field2` = TRIM(%s),
                         `add-field3` = TRIM(%s),
                         `add-field4` = TRIM(%s),
                         `add-field5` = TRIM(%s),                         
                         `last_mod_date` = \'' .myi_getDatetimeNow() .'\',   
                         `last_mod_by_id` = %d  
                     WHERE a.`prod_id` = ' .$this->prod_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product` 
                         ( `action`, `prod_id`, `prod_cd`, `prod_name`, `prod_desc`, `prod_dimension`, `prod_img_url`, 
                           `prod_remark`, `logo_lang`, `add-field1`, `add-field2`, `add-field3`,
                           `add-field4`, `add-field5`, `create_date`, `create_by_id` )
                          values
                          ( \'Update\', ' .$this->prod_id .', TRIM(\'' .addslashes($this->prod_cd) .'\'), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), TRIM(%s), 
                            TRIM(%s), TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d );';  

                          
            try {                  
                // begin transaction
                $wpdb->query('START TRANSACTION');
                $rows_inserted = $wpdb->query( $wpdb->prepare( $stmt,
                                                               $prod_name,
                                                               $prod_desc, 
                                                               $prod_dimension, 
                                                               $prod_img_url, 
                                                               $prod_remark, 
                                                               $logo_lang, 
                                                               $add_field1, 
                                                               $add_field2, 
                                                               $add_field3, 
                                                               $add_field4, 
                                                               $add_field5, 
                                                               $create_by ));              
                                          
                $rows_inserted_log = $wpdb->query( $wpdb->prepare( $log_stmt,
                                                                   $prod_name, 
                                                                   $prod_desc, 
                                                                   $prod_dimension, 
                                                                   $prod_img_url, 
                                                                   $prod_remark, 
                                                                   $logo_lang, 
                                                                   $add_field1, 
                                                                   $add_field2, 
                                                                   $add_field3, 
                                                                   $add_field4, 
                                                                   $add_field5, 
                                                                   $create_by ));
                
                if ( $rows_inserted && $rows_inserted_log ) {
                    $this->prod_name =  $prod_name;
                    $this->prod_desc =  $prod_desc;
                    $this->prod_dimension =  $prod_dimension;
                    $this->prod_img_url =  $prod_img_url;
                    $this->prod_remark =  $prod_remark;
                    $this->logo_lang =  $logo_lang;
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
        } // mod_product       

       /**
		* delete the product 
		*
        * @param int $delete_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @return int/bool clients deleted and false if fail
		*/          
        public function del_product( $delete_by, $user_roles = null  ) {         
            if ( $this->prod_id === null || $this->prod_id == 0 ) {
               return false;
            }            
           
            global $wpdb;                  

            // ensure that it is not been in used 
            $stmt = 'SELECT prod_id 
                     FROM `' .$wpdb->prefix .'myi_mst_product_uom` 
                     WHERE prod_id = ' .$this->prod_id .'
                        and deleted = 0
                     UNION
                     SELECT prod_id 
                     FROM `' .$wpdb->prefix .'myi_mst_category_product` 
                     WHERE prod_id = ' .$this->prod_id .'
                        and deleted = 0';

            if ( count($wpdb->get_results( $stmt )) > 0 ) {
                return false;
            };                          
                     
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_product` a 
                     INNER JOIN ( SELECT max(`delete_inventory_master`) as `delete_inventory_master`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.delete_inventory_master = 1
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_product` 
                                 WHERE deleted <> 0
                                    and prod_cd = TRIM(\'' .addslashes($this->prod_cd) .'\')
                                ) z
                     SET deleted = z.cnt + 1,
                         `delete_date` = \'' .myi_getDatetimeNow() .'\',   
                         `delete_by_id` = %d 
                     WHERE a.`prod_id` = ' .$this->prod_id .' 
                        and a.deleted = 0';
                       
            $log_stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_logm_product` 
                         ( `action`, `prod_id`, `prod_cd`, `create_date`, `create_by_id` )
                          values
                          ( \'Delete\', ' .$this->prod_id .', TRIM(\'' .addslashes($this->prod_cd) .'\'),\'' .myi_getDatetimeNow() .'\', %d );';  

                          
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
        } // del_product                    
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_Product already exists. Action aborted...'); 
} // Myi_Product
?>