<?php
namespace my_inventory\myi_copy_roles;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_user.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_user_client_roles.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_client.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  

    // already retrieved the allowed client_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'mod_roles' ) {
        $user_list = stripslashes($_POST['user_list']);
        $user_cd_list = stripslashes($_POST['user_cd_list']);
        $user_name_list = stripslashes($_POST['user_name_list']);        
    } else { // get the allowed client_list        
        $user_obj = new \my_inventory\Myi_User();
        $users = $user_obj->get_all_users();
        
        if ($users === false) {
            echo 'Error obtaining users lists<br/><br/>';
        }

        $user_list = $users[0];
        $user_cd_list = $users[1];
        $user_name_list = $users[2];  
    }
    
    if (isset($_POST['user-select']) && $_POST['user-select'] != 0
             && $_POST['user-select-to'] != 0 && wp_verify_nonce( $_POST['copy_roles_nonce'], 'copy_roles' )) {
            $user_roles = new \my_inventory\Myi_User_Roles();

            $cnt_succ = 0;
            $cnt_fail = 0;

            $result = $user_roles->copy_role(   $_POST['user-select'], 
                                                $_POST['user-select-to'],
                                                get_current_user_id(), 
                                                $user_roles->get_role_ignore_client( get_current_user_id() ) .',' .(current_user_can( 'create_users' ) ?
                                                                                                                    '\'myi_store_manager\'' : '') );

            if ( !$result ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Copy failed... Ensure that the copied to user do not have repeated roles</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Roles copied...</span><br/>';
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="mod_roles" />
    <input type="hidden" name="user_list" id="user_list" value="<?php echo htmlspecialchars($user_list); ?>" />
    <input type="hidden" name="user_cd_list" id="user_cd_list" value="<?php echo htmlspecialchars($user_cd_list); ?>" />
    <input type="hidden" name="user_name_list" id="user_name_list" value="<?php echo htmlspecialchars($user_name_list); ?>" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'copy_roles', 'copy_roles_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
                <tr>
        				<td>User</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="user-select" name="user-select" data-live-search="true" 
                             onchange="this.form.submit();">
                            <option data-content="None Selected">0</option>
                        <?php 
                        $user_arr = array( explode( ',', $user_list ), explode( '~|`', $user_cd_list), explode( '~|`', $user_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $user_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($user_arr[1][$cnt]) .'   ' 
                                  .( $user_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($user_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['user-select']) && $_POST['user-select'] == $user_arr[0][$cnt] ? ' selected=selected ' : '' )
                                    .'>'
                                    .$user_arr[0][$cnt] .'</option>';
                        }
                        ?>
                        </select>
                        </td>                        
                </tr>
       			<tr>
        				<td>User Copied To</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="user-select-to" name="user-select-to" data-live-search="true" 
                             >
                            <option data-content="None Selected">0</option>
                        <?php 
                        $user_arr = array( explode( ',', $user_list ), explode( '~|`', $user_cd_list), explode( '~|`', $user_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $user_arr[0] ); $cnt++ ) {
                          if ( isset($_POST['user-select']) && $user_arr[0][$cnt] != $_POST['user-select'] ) {
                            echo '<option data-content="'.htmlspecialchars($user_arr[1][$cnt]) .'   ' 
                                  .( $user_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($user_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['user-select-to']) && $_POST['user-select-to'] == $user_arr[0][$cnt] ? ' selected=selected ' : '' )
                                    .'>'
                                    .$user_arr[0][$cnt] .'</option>';
                          }
                        }
                        ?>
                        </select>
                        </td>
                </tr>
                        <tr><td></td><td></td>
                        </tr>
                        <tr>
                            <td></td><td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-copy">    Copy</span>
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
