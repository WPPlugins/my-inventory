<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_User' ) ) { 
    class Myi_User { 
        private $user_id, $user_login, $user_pass, $user_email, $display_name, $description;
        
        public function __construct() {
            $this->user_id = null;
            $this->user_login = null;
            $this->user_pass = null;
            $this->user_email = null;
            $this->display_name = null;
            $this->description = null;
        }
        
        public function get_user_id() {
            return $this->user_id;
        }

        public function get_user_login() {
            return $this->user_login;
        }

        public function get_user_pass() {
            return $this->user_pass;
        }

        public function get_user_email() {
            return $this->user_email;
        }

        public function get_display_name() {
            return $this->display_name;
        }

        public function get_description() {
            return $this->description;
        }

       /**
		* get the user particular
        *  @param string $user_id the user_id       
		*
		* @return int 1 if successful and false if fail
		*/          
        public function get_all_users_by_id( $user_id ) {                    
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }

            global $wpdb;

            $stmt = 'SELECT a.*, b.meta_value as deleted, c.meta_value as description
                     FROM `' .$wpdb->prefix .'users` a 
                     left join `' .$wpdb->prefix .'usermeta` b 
                        on a.ID = b.user_id
                        and b.meta_key = \'deleted\'
                     left join `' .$wpdb->prefix .'usermeta` c 
                        on a.ID = c.user_id
                        and c.meta_key = \'description\'                        
                     WHERE ifnull(b.meta_value,0) = 0
                        and a.ID = %d';
                     
            $my_results =  $wpdb->get_results( $wpdb->prepare( $stmt,
                                                               $user_id ));

            if ( !$my_results ) {
                return false;
            }

            $this->user_id = $my_results[0]->ID;
            $this->user_login = $my_results[0]->user_login;
            $this->user_pass = $my_results[0]->user_pass; // encoded
            $this->user_email = $my_results[0]->user_email;
            $this->display_name = $my_results[0]->display_name;
            $this->description = $my_results[0]->description;

            return 1;
        } // get_all_users_by_id

       /**
		* get all the users (used only for assigning users as will not check if have rights to read
        * eg first time user creation
		*
		* @return array of client_id separated by comma, client_cd separated by delimiter group ~|`, client_name separated by delimiter group ~|` and false if fail
		*/          
        public function get_all_users() {                    
            global $wpdb;

            $stmt = 'SELECT *
                     FROM `' .$wpdb->prefix .'users` a 
                     left join `' .$wpdb->prefix .'usermeta` b 
                        on a.ID = b.user_id
                        and b.meta_key = \'deleted\'
                     WHERE ifnull(b.meta_value,0) = 0';
                     
            $my_results =  $wpdb->get_results( $stmt );       
                                                              
            if ( !$my_results ) {
                return false;
            }

            $client_list = '';
            $client_cd_list = '';
            $client_name_list = '';

            foreach ($my_results as $row) {
                $client_list .= $row->ID .',';
                $client_cd_list .= $row->user_login .'~|`';
                $client_name_list .= $row->display_name .'~|`';
            }

            $client_list = substr( $client_list, 0, -1 );
            $client_cd_list = substr( $client_cd_list, 0, -3 );
            $client_name_list = substr( $client_name_list, 0, -3 );

            return array( $client_list, $client_cd_list, $client_name_list );
        } // get_all_users  
        
        /**
        *  Add a user into wp core user tables
        *
        *  @param string $user_login the user login name
        *  @param string $user_pass the user password
        *  @param string $user_email the user email address
        *  @param string $display_name the user name
        *  @param string $description any other information
        *  @param string $user_roles user_roles
        *  @return int user_Id if successful, 0 if failed
        */        
        public function add_user ( $user_login, $user_pass, $user_email, $display_name, $description = null, $user_roles = null ) {
            if ( $description === null ) {
                $description = '';
            }
            
            // check if have rights to add user
            global $wpdb;
            
            $stmt = 'SELECT max(`create_user`) as `create_user`
                     FROM `' .$wpdb->prefix .'myi_mst_roles`  
                     WHERE role_id in (\'\',' .$user_roles .')';
                     
            $my_result =  $wpdb->get_results( $stmt );
            
            if ( !$my_result || $my_result[0]->create_user == 0 ) {
                return false;
            }
            
            
            $userdata = array(
                'user_login'  =>  trim($user_login),
                'user_pass'   =>  trim($user_pass),
                'user_email'  =>  trim($user_email),
                'display_name'=>  trim($display_name),
                'description' =>  $description,
                'role'        =>  'subscriber',
            );

            $user_id = wp_insert_user( $userdata ) ;

            //On success
            if ( ! is_wp_error( $user_id ) ) {
                if ( add_user_meta( $user_id, 'deleted', '0') === false ) { //failed
                    wp_delete_user( $user_id );
                    return false;
                } else {
                    return $user_id;
                }
            } else {
                return false;
            }
        }

        /**
        *  Edit a user into wp core user tables
        *
        *  @param int $user_id the user_id
        *  @param string $user_pass the user password. Password must be unhashed
        *  @param string $user_email the user email address
        *  @param string $display_name the user name
        *  @param string $description any other information
        *  @param string $user_roles user_roles
        *  @return int user_Id if successful, 0 if failed
        */        
        public function mod_user ( $user_id, $user_pass, $user_email, $display_name, $description = null, $user_roles = null ) {
            if ( $user_id === null || $user_id == 0 ) {
                return false;
            }
            
            if ( $description === null ) {
                $description = '';
            }
            
            // check if have rights to mod user
            global $wpdb;
            
            $stmt = 'SELECT max(`mod_user`) as `mod_user`
                     FROM `' .$wpdb->prefix .'myi_mst_roles`  
                     WHERE role_id in (\'\',' .$user_roles .')';
                     
            $my_result =  $wpdb->get_results( $stmt );

            if ( !$my_result || $my_result[0]->mod_user == 0 ) {
                return false;
            }
            
            
            $userdata = array(
                'ID'          =>  $user_id,
                'user_email'  =>  trim($user_email),
                'display_name'=>  trim($display_name),
                'description' =>  $description,
            );

            if ( $user_pass !== null && trim($user_pass) != '' ) {
                $new_elem = array( 'user_pass' => trim($user_pass) );
                $userdata = array_merge( $userdata, $new_elem );
            }



            $user = wp_update_user( $userdata ) ;

            //On success
            if ( ! is_wp_error( $user ) ) {
                    return $user;
            } else {
                return false;
            }
        } // mod_user

        /**
        *  Delete a user into wp core user tables (logical deletion)
        *
        *  @param int $user_id the user_id
        *  @param string $delete_by delete by
        *  @param string $user_roles user_roles
        *  @return int user_Id if successful, 0 if failed
        */        
        public function del_user ( $user_id, $delete_by, $user_roles = null ) {
            if ( $user_id === null || $user_id == 0 || $delete_by === null || $delete_by == 0 ) {
                return false;
            }
            
            if ( $description === null ) {
                $description = '';
            }
            
            // check if have rights to del user
            global $wpdb;
            
            $stmt = 'SELECT max(`delete_user`) as `delete_user`
                     FROM `' .$wpdb->prefix .'myi_mst_roles`  
                     WHERE role_id in (\'\',' .$user_roles .')';
                     
            $my_result =  $wpdb->get_results( $stmt );

            if ( !$my_result || $my_result[0]->delete_user == 0 ) {
                return false;
            }

            $result = update_user_meta( $user_id, 'deleted', '1' );

            if ( ! $result ) {
                return false;
            };

            $stmt = 'UPDATE `' .$wpdb->prefix .'users` 
                        SET user_login = concat(user_login,\'(\',ID,\')\') ,
                            user_email = concat(user_email,\'(\',ID,\')\') 
                     WHERE ID = %d';


                                                        
            $my_result =  $wpdb->query( $wpdb->prepare( $stmt, 
                                                        $user_id ));

            if (!$my_result) {
                return false;
            }

            $stmt = 'UPDATE `' .$wpdb->prefix .'myi_mst_user_client_role` 
                        SET deleted = 1,
                            delete_date = \'' .myi_getDatetimeNow() .'\',
                            delete_by_id = %d
                     WHERE user_id = %d
                            and deleted = 0;';
                     
            $my_result = $wpdb->query( $wpdb->prepare( $stmt,
                                                       $delete_by,
                                                       $user_id ));
            
            return $user_id;
        } // del_user
    }
} else {
    throw new \Exception('Class \\my_inventory\\Myi_User already exists. Action aborted...'); 
} // Myi_User
?>