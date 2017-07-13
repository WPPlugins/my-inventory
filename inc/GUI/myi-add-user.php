<?php
namespace my_inventory\myi_add_user;

include_once(ABSPATH.'wp-admin/includes/user.php');

require_once( plugin_dir_path( __FILE__ ) . '/../myi_user.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_user_client_roles.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  
    
    if (isset($_POST['login_id']) && trim($_POST['login_id'] != '') && wp_verify_nonce( $_POST['add_user_nonce'], 'add_user' )) {
            $client_rec = new \my_inventory\Myi_User();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            $user_id = $client_rec->add_user ( stripslashes($_POST['login_id']),
                                               stripslashes($_POST['pass']),
                                               stripslashes($_POST['email']),
                                               stripslashes($_POST['user_name']),
                                               stripslashes($_POST['desc']),
                                               $user_roles->get_role_ignore_client( get_current_user_id() ).',' .(current_user_can( 'create_users' ) ?
                                                                                                                    '\'myi_store_manager\'' : ''));

            $access_roles_succeed = true;

            if ( isset( $_POST['cb'] ) && $user_id ) {
                foreach( $_POST['cb'] as $role_id ) {
                    // if user has word press create_user rights, will treat as if myi_store_manager for this page
                    $result = $user_roles->add_role( $user_id, 
                                                     $role_id, 
                                                     get_current_user_id(), 
                                                     $user_roles->get_role_ignore_client( get_current_user_id() ) .',' .(current_user_can( 'create_users' ) ?
                                                                                                                    '\'myi_store_manager\'' : '')
                                                     
                                                     ) ;
                                                    
                    if ( !$result ) {
                        $access_roles_succeed = false;
                        break;
                    }
                }
            }

            if ( !$user_id || !$access_roles_succeed ) {
                if ( $user_id ) {
                    wp_delete_user( $user_id );
                }
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to create user and that the user did not exist already.</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    User saved...</span><br/>';
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_user" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'add_user', 'add_user_nonce' ); ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields" for="login_id">Login ID :</label></td>
                            <td><input type="text" class="form-control input_values" id="login_id" name="login_id" required maxlength="60"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="user_name">Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="user_name" name="user_name" required maxlength="250"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="pass">Password :</label></td>
                            <td><input type="text" class="form-control input_values" id="pass" name="pass" required maxlength="255" data-rule-minlength="8"
                                    value="<?php echo wp_generate_password( $length=12, $include_standard_special_chars=true ); ?>"><span class="glyphicon glyphicon-asterisk required">Required</span></input></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="email">Email :</label></td>
                            <td><input type="text" class="form-control input_values" id="email" name="email" required maxlength="100" data-rule-email="true"><span class="glyphicon glyphicon-asterisk required">Required</span></input></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="desc">Description :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="desc" name="desc" maxlength="4000"></textarea></td>
                        </tr>  
                        <tr><td></td><td></td>
                        <tr><td colspan=2><label class="header-highlight">User Access Roles (Default for all clients)</label></td>
                        </tr>
                        <?php
                            $roles = new \my_inventory\Myi_User_Roles();
                            $all_roles = $roles->get_all_roles();

                            for ( $c = 0; $c < count( $all_roles); $c++ ) {
                                echo '<tr><td><div class="checkbox">
                                    <label><input type="checkbox" value="' .$all_roles[$c][1] .'" id="cb[]" name="cb[]"></label>
                                </div></td>';
                                
                                echo '<td><label class="label_values">' .$all_roles[$c][0] .'</label></td></tr>';
                            }
                        ?>
                        <tr><td></td><td></td>
                        </tr>
                        <tr>
                            <td></td><td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save">    Save</span>
                                        </button></td>
                        </tr>
                        </tbody>
                    </table>            
                </div>    
</div>                
</form>

<?php
} //user logged in
//wp_footer();
?>
