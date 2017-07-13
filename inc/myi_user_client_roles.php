<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_User_Roles' ) ) { 
    // if there is roles for client_id (other than 1), the client_id role will take priority.
    // Otherwise will use the role for client_id 1 (which is the default role.)
    class Myi_User_Roles {         
        private $user_id, $client_id, $roles;
        
        public function __construct() {
            $this->user_id = null;
            $this->client_id = null;
            $this->roles = null;
        }
        
        /**
        * get the saved roles
        *
        * @return string/bool string of roles and false if fail
        */
        public function get_saved_role(){
            if ( $this->user_id === null ) {
                return false;
            }
            
            return $this->roles;
        }

        /**
        *  Convert role_id to display name
        *
        *  @param string $role_id the role_id to convert
        *  @return string the display name
        */
        public function convert_role_id_to_display( $role_id ) {
            return (ucwords(str_replace('_',' ',substr($role_id,4))));
        }

        /**
        * Get all the roles for the user
        *
        *  @param int $user_id the user_id
        *  @return array of records of the user roles
        */
        public function get_roles( $user_id ) {
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }
            
            global $wpdb;
            
            $stmt = 'SELECT a.role_id, b.client_id, b.client_cd, b.client_name, a.id
                     FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                     INNER JOIN `' .$wpdb->prefix .'myi_mst_client` b 
                        on a.client_id = b.client_id
                     WHERE a.deleted = 0
                        and a.user_id = %d
                     ORDER BY b.client_id, a.role_id
                     ';

            $my_results = $wpdb->get_results( $wpdb->prepare ( $stmt,
                                                               $user_id ));

            return $my_results;
        }
        
        /**
        * Return an array of array of display name and actual role_id
        * Display name is obtained via taking out the first 4 letter of role_id and replacing _ with space and capitalize first alphabet of each word
        */
        public function get_all_roles() {
            global $wpdb;
            
            $stmt = 'SELECT role_id
                     FROM `' .$wpdb->prefix .'myi_mst_roles` a 
                     ';

            $my_results = $wpdb->get_results( $stmt );

            if ( !$my_results ) {
                return false;
            } else {
                $arr = array();
                
                foreach ($my_results as $row) {
                    array_push($arr, array( $this->convert_role_id_to_display( $row->role_id ), $row->role_id));
                }
                
                return $arr;
            }  
        }
        
        /**
        *  Check that a column name is a valid column name in myi_role table
        *
        *  @param string $tab_name table name to check against (will not do trim)
        *  @param string $col_name column name to check (will not do trim)
        *  @return string column name if in table, otherwise return empty string
        */
        public function is_column_in_table( $tab_name, $col_name ) {
            global $wpdb;
            
            $stmt = 'SELECT d.column_name
                     FROM information_schema.columns d
                     WHERE d.table_schema = \'' .$wpdb->dbname .'\'
                        and d.table_name = %s
                        and d.column_name = %s';
                                                
            if ( !$wpdb->query( $wpdb->prepare( $stmt,
                                                $tab_name,
                                                $col_name ) ) ) {
                return '';
            } else {
                return $col_name;
            }            
        }
        
        /**
		* get the roles of the users 
		*
        * @param int $user_id the user_id
        * @return string/bool string of roles separated by comma eg '','myi_client','myi_staff' and false if fail
		*/          
        public function get_role_ignore_client( $user_id ) {
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }            
                       
            global $wpdb;

            // priority 1 is always the role_id for the client_id
            // priority 5 is always the role_id for client_id = 1
            // if no role_id set for that client_id, will use the role_id for client_id = 1
            $stmt = '       SELECT distinct e.role_id
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` e 
                            WHERE e.deleted = 0
                                and e.user_id = %d 
                     ';
            
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $user_id,                                                   
                                                              $user_id ));  


            $string = '\'\'';
            
            for ( $cnt = 0; $cnt < count( $my_results ); $cnt++ ) {
                    $string = $string .',\'' .$my_results[$cnt]->role_id .'\'';
            }
            
            $this->user_id = $user_id;
            $this->roles = $string;

            return $string;
        } // get_role_ignore_client
        
        /**
		* get the roles of the users for that client_id
        * if there is no roles set for that client_id, will use the roles for client_id = 1
        * otherwise will get the role for that client_id
		*
        * @param int $user_id the user_id
        * @param int $client_id the client_id the access right is for. If not passed in, will default to 1
        * @return string/bool string of roles separated by comma eg '','myi_client','myi_staff' and false if fail
		*/          
        public function get_role( $user_id, $client_id = null ) {
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }            
            
            if ( $client_id === null ) {
                $client_id = 1;
            }
            
            global $wpdb;

            // priority 1 is always the role_id for the client_id
            // priority 5 is always the role_id for client_id = 1
            // if no role_id set for that client_id, will use the role_id for client_id = 1
            $stmt = 'SELECT f.role_id
                     FROM (
                        SELECT min(c.priority) as min_priority
                        FROM (
                            SELECT a.role_id, case when a.client_id = 1 
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                            WHERE a.deleted = 0
                                and a.user_id = %d 
                                and a.client_id in (1, %d)                        
                        ) c 
                      ) d 
                      INNER JOIN (
                            SELECT e.role_id, case when e.client_id = 1 
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` e 
                            WHERE e.deleted = 0
                                and e.user_id = %d 
                                and e.client_id in (1, %d)                        
                      ) f 
                        on f.priority = d.min_priority;
                     ';
            
            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $user_id,
                                                              $client_id,
                                                              $user_id, 
                                                              $client_id ));  


            $string = '\'\'';
            
            for ( $cnt = 0; $cnt < count( $my_results ); $cnt++ ) {
                    $string = $string .',\'' .$my_results[$cnt]->role_id .'\'';
            }
            
            $this->user_id = $user_id;
            $this->client_id = $client_id;
            $this->roles = $string;
            
            return $string;
        } // get_role        
        
        /**
		* add in new roles 
		*
        * @param int $user_id the user_id
		* @param string $role_id The role_id
        * @param int $create_by The user who create it
        * @param string $user_roles The access rights of the user who create
        * @param int $client_id the client_id the access right is for. If not passed in, will default to 1
        * @return int/bool roles inserted and false if fail
		*/          
        public function add_role( $user_id, $role_id, $create_by, $user_roles = null, $client_id = null ) {
            if ( $user_id === null || $user_id == 0 || $role_id === null ) {
                return false;
            }            

            if ( $client_id === null ) {
                $client_id = 1;
            }
            
            global $wpdb;

            // begin transaction
            $wpdb->query('START TRANSACTION');

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_user_client_role` 
                     ( `user_id`, `client_id`, `role_id`, `create_date`, `create_by_id` )
                     SELECT %d, %d, TRIM(%s), \'' .myi_getDatetimeNow() .'\', %d
                     FROM `' .$wpdb->prefix .'users` a
                     INNER JOIN ( SELECT max(`mod_roles`) as `mod_roles`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_roles = 1
                     WHERE a.`ID` = %d';

            $result = $wpdb->query( $wpdb->prepare( $stmt,
                                                 $user_id, 
                                                 $client_id, 
                                                 $role_id,
                                                 $create_by,
                                                 $user_id ));
            
            // set the role of the user
            if ( $result ) {
                $stmt = 'SELECT min(wp_role) as wp_role
                         FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                         INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                            ON a.role_id = b.role_id
                         WHERE a.user_id = %d 
                            AND a.deleted = 0';


                $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                                  $user_id));

                if ( !$my_results ) {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
                
                $userdata = array(
                    'ID'          =>  $user_id,
                    'role'  =>  $my_results[0]->wp_role,
                );

                $user = wp_update_user( $userdata ) ;

                //On success
                if ( ! is_wp_error( $user ) ) {
                    $wpdb->query('COMMIT'); // something went wrong, Rollback
                    return $result;
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
            } 
            
            return $result;
        } // add_role
        
        /*
        * copy role from 1 user_id to another
        *
        * @param int $user_id_from copy from user_id
        * @param int $user_id_to copy to user_id     
        * @param int $create_by The user who create it      
        * @param string $user_roles The access rights of the user who create        
        * @return int/bool roles inserted and false if fail        
        */
        public function copy_role( $user_id_from, $user_id_to, $create_by, $user_roles = null ) {
            if ( $user_id_from === null || $user_id_from == 0 || $user_id_to === null || $user_id_to == 0 ) {
                return false;
            }            
            
            global $wpdb;
            
            // begin transaction
            $wpdb->query('START TRANSACTION');

            $stmt = 'INSERT INTO `' .$wpdb->prefix .'myi_mst_user_client_role` 
                     ( `user_id`, `client_id`, `role_id`, `create_date`, `create_by_id` )
                     SELECT %d, a.`client_id`, a.`role_id`, \'' .myi_getDatetimeNow() .'\', %d 
                     FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a
                     INNER JOIN ( SELECT max(`mod_roles`) as `mod_roles`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_roles = 1                     
                     WHERE a.`user_id` = %d
                        and a.deleted = 0';

            $result = $wpdb->query( $wpdb->prepare( $stmt,
                                                 $user_id_to,  
                                                 $create_by,
                                                 $user_id_from ));
                                                 
            // set the role of the user
            if ( $result ) {
                $stmt = 'SELECT min(wp_role) as wp_role
                         FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                         INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                            ON a.role_id = b.role_id
                         WHERE a.user_id = %d 
                            AND a.deleted = 0';


                $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                                  $user_id_to));

                if ( !$my_results ) {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
                
                $userdata = array(
                    'ID'          =>  $user_id_to,
                    'role'  =>  $my_results[0]->wp_role,
                );

                $user = wp_update_user( $userdata ) ;

                //On success
                if ( ! is_wp_error( $user ) ) {
                    $wpdb->query('COMMIT'); // something went wrong, Rollback
                    return $result;
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
            } 
            
            return $result;
        } // copy_role        
        
        /*
        * delete row based on $client_id, $user_id, $role_id,
        *
        * @param int $user_id the user_id 
        * @param int $client_id the client_id
        * @param string $role_id the role_id
        * @param int $delete_by The user who delete it    
        * @param string $user_roles The access rights of the user who create              
        * @return int/bool roles deleted and false if fail        
        */
        public function delete_role( $user_id, $client_id, $role_id, $delete_by, $user_roles = null ) {
            if ( $user_id === null || $user_id == 0 || $client_id === null || $client_id == 0 || $role_id === null ) {
                return false;
            }            
            
            global $wpdb;
            
            // begin transaction
            $wpdb->query('START TRANSACTION');
            
            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                     INNER JOIN ( SELECT max(`mod_roles`) as `mod_roles`
                                  FROM `' .$wpdb->prefix .'myi_mst_roles`  
                                  WHERE role_id in (\'\',' .$user_roles .')) c 
                        on c.mod_roles = 1   
                     CROSS JOIN ( SELECT IFNULL(count(1),0) as cnt FROM `' .$wpdb->prefix .'myi_mst_user_client_role` 
                                 WHERE deleted <> 0
                                    and client_id = %d
                                    and role_id = TRIM(%s)
                                    and user_id = %d
                                ) z
                     SET deleted = z.cnt + 1,
                         delete_date = \'' .myi_getDatetimeNow() .'\',
                         delete_by_id = %d
                     WHERE a.user_id = %d
                        and a.client_id = %d
                        and a.role_id = TRIM(%s)
                        and a.deleted = 0';

            $result = $wpdb->query( $wpdb->prepare( $stmt,
                                                 $client_id, 
                                                 $role_id, 
                                                 $user_id, 
                                                 $delete_by,
                                                 $user_id,
                                                 $client_id, 
                                                 $role_id ));

            // set the role of the user
            if ( $result ) {
                $stmt = 'SELECT min(wp_role) as wp_role
                         FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                         INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                            ON a.role_id = b.role_id
                         WHERE a.user_id = %d 
                            AND a.deleted = 0';


                $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                                  $user_id));

                if ( !$my_results ) {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
                
                $userdata = array(
                    'ID'          =>  $user_id,
                    'role'  =>  $my_results[0]->wp_role,
                );

                $user = wp_update_user( $userdata ) ;

                //On success
                if ( ! is_wp_error( $user ) ) {
                    $wpdb->query('COMMIT'); // something went wrong, Rollback
                    return $result;
                } else {
                    $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                    return false;
                }
            } 
            
            return $result;
        } // delete_role

        /*
        * get all the access rights for a particular user
        *
        * @param int $user_id the user_id 
        * @return string/bool access rights separated by comma and false if fail        
        */
        public function get_access_rights( $user_id ) {
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }

            global $wpdb;

            $stmt = 'SELECT max(b.view_inventory) as view_inventory,
                            max(b.stock_mod_inventory) as stock_mod_inventory,
                            max(b.create_inventory) as create_inventory,
                            max(b.mod_inventory) as mod_inventory,
                            max(b.delete_inventory) as delete_inventory,
                            max(b.view_inventory_master) as view_inventory_master,
                            max(b.create_inventory_master) as create_inventory_master,
                            max(b.mod_inventory_master) as mod_inventory_master,
                            max(b.delete_inventory_master) as delete_inventory_master,
                            max(b.create_user) as create_user,
                            max(b.mod_user) as mod_user,
                            max(b.delete_user) as delete_user,
                            max(b.mod_roles) as mod_roles,
                            max(b.view_logs) as view_logs,
                            max(b.view_reports) as view_reports
                     FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                     INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                        on a.role_id = b.role_id
                     WHERE a.user_id = %d
                        and a.deleted = 0';

            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $user_id ));

            if ( !$my_results ) {
                return false;
            }

            $rights = ',';
            $rights = $rights .( $my_results[0]->view_inventory == 1 ? 'view_inventory,' : '' );
            $rights = $rights .( $my_results[0]->stock_mod_inventory == 1 ? 'stock_mod_inventory,' : '' );
            $rights = $rights .( $my_results[0]->create_inventory == 1 ? 'create_inventory,' : '' );
            $rights = $rights .( $my_results[0]->mod_inventory == 1 ? 'mod_inventory,' : '' );
            $rights = $rights .( $my_results[0]->delete_inventory == 1 ? 'delete_inventory,' : '' );
            $rights = $rights .( $my_results[0]->view_inventory_master == 1 ? 'view_inventory_master,' : '' );
            $rights = $rights .( $my_results[0]->create_inventory_master == 1 ? 'create_inventory_master,' : '' );
            $rights = $rights .( $my_results[0]->mod_inventory_master == 1 ? 'mod_inventory_master,' : '' );
            $rights = $rights .( $my_results[0]->delete_inventory_master == 1 ? 'delete_inventory_master,' : '' );
            $rights = $rights .( $my_results[0]->create_user == 1 ? 'create_user,' : '' );
            $rights = $rights .( $my_results[0]->mod_user == 1 ? 'mod_user,' : '' );
            $rights = $rights .( $my_results[0]->delete_user == 1 ? 'delete_user,' : '' );
            $rights = $rights .( $my_results[0]->mod_roles == 1 ? 'mod_roles,' : '' );
            $rights = $rights .( $my_results[0]->view_logs == 1 ? 'view_logs,' : '' );
            $rights = $rights .( $my_results[0]->view_reports == 1 ? 'view_reports,' : '' );

            return $rights;
        } // get_access_rights       

        /*
        * get all the client_id that the user has access rights to
        *
        * @param int $user_id the user_id 
        * @param string $access the column name of the access rights 
        * @param string $client_cd_allowed the client_cd allowed to access. If null, will allow all that the user have access rights to
        * @return string/bool array of client_id separated by comma, client_cd separated by delimiter group ~|`, client_name separated by delimiter group ~|` and false if fail        
        */
        public function get_clients( $user_id, $access, $client_cd_allowed = null ) {
            if ( $user_id === null || $user_id == 0 || $access === null ) {
                return false;
            }

            global $wpdb;


            // priority 1 is always for the client_id other than client_id = 1
            // priority 5 is always for client_id = 1
            // if for that client_id, no access is set for $access, will not display that client_id
            // however, if no client_id is set but client_id = 1 have access, will display that client_id
            $stmt = 'SELECT h.client_id, i.client_cd, i.client_name
                     FROM (
                        SELECT min(f.priority) as min_priority, f.client_id
                        FROM (
                            SELECT a.client_id, max(' .$this->is_column_in_table($wpdb->prefix .'myi_mst_roles',$access) .') as have_rights,
                                              case when a.client_id = 1
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                                on a.role_id = b.role_id
                            WHERE a.deleted = 0
                                and a.user_id = %d   
                                and a.client_id <> 1
                            GROUP BY a.client_id
                            union
                            SELECT e.client_id, max(' .$this->is_column_in_table($wpdb->prefix .'myi_mst_roles',$access) .') as have_rights,
                                              case when c.client_id = 1 
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` c 
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` d 
                                on c.role_id = d.role_id
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_client` e
                                on e.deleted = 0
                            WHERE c.deleted = 0
                                and c.user_id = %d   
                                and c.client_id = 1
                            GROUP BY e.client_id                            
                        ) f                        
                        GROUP BY f.client_id
                      ) g 
                      INNER JOIN (
                            SELECT a.client_id, max(' .$this->is_column_in_table($wpdb->prefix .'myi_mst_roles',$access) .') as have_rights,
                                              case when a.client_id = 1 
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` b 
                                on a.role_id = b.role_id
                            WHERE a.deleted = 0
                                and a.user_id = %d   
                                and a.client_id <> 1
                            GROUP BY a.client_id
                            union
                            SELECT e.client_id, max(' .$this->is_column_in_table($wpdb->prefix .'myi_mst_roles',$access) .') as have_rights,
                                              case when c.client_id = 1 
                                                   then 5
                                                   else 1
                                              END as priority
                            FROM `' .$wpdb->prefix .'myi_mst_user_client_role` c 
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_roles` d 
                                on c.role_id = d.role_id
                            INNER JOIN `' .$wpdb->prefix .'myi_mst_client` e
                                on e.deleted = 0
                            WHERE c.deleted = 0
                                and c.user_id = %d   
                                and c.client_id = 1
                            GROUP BY e.client_id                          
                      ) h 
                        on h.priority = g.min_priority
                        and h.have_rights = 1
                        and h.client_id = g.client_id
                      INNER JOIN `' .$wpdb->prefix .'myi_mst_client` i 
                        on i.client_id = h.client_id
                        and i.deleted = 0
                      WHERE trim(i.client_cd) = if( 1=' .(is_null($client_cd_allowed) ? '1' : '0') .',trim(i.client_cd),trim(%s))  
                      ORDER BY i.client_cd
                     ';
                                                            

            $my_results = $wpdb->get_results( $wpdb->prepare( $stmt,
                                                              $user_id,
                                                              $user_id,
                                                              $user_id,
                                                              $user_id,
                                                              $client_cd_allowed ));

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
        } // get_clients            
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_User_Roles already exists. Action aborted...'); 
} // Myi_User_Roles
?>