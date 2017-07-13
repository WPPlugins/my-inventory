<?php
if ( ! function_exists( 'myi_get_user_role' ) ) {
    /*
     * Get user's role
     * Author : Kellen Mace
     *
     * No longer in use as we based on roles checking from our custom tables instead of wordpress.
     *
     * If $user parameter is not provided, returns the current user's role.
     * If $role_name_prefix is provided, return the first role name starting with the prefix. If not provided, return the first role
     * Only returns the user's first role, even if they have more than one.
     * Returns false on failure.
     *
     * @param  string      $role_name_prefix only retrieve roles_names that begin with the prefix
     * @param  mixed       $user User ID or object.     
     * @return string      The User's role, or `~!@#$%^&*No Roles` on failure.
     */
    function myi_get_user_role( $role_name_prefix = null, $user = null ) {
        $user = $user ? new WP_User( $user ) : wp_get_current_user();    
        
        $roles = '';
        
        foreach( $user->roles as $key => $value )
        {       
           if( $role_name_prefix == null || !( strpos( $value, $role_name_prefix ) === false ) )
           {
                $roles = '\'' .$value .'\',';
           }
        }
        
        if ( $roles == '' ) { // no roles found
            return '\'~!@#$%^&*No Roles\'';
        } else {
            return substr( $roles, 0, -1 ); // return the roles removing the trailing comma
        }
    } 
} else {
    throw new Exception('Function myi_get_user_role already exists. Action aborted...'); 
} //myi_get_user_role
?>