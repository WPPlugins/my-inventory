<?php
namespace my_inventory\myi_assign_roles;

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
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);

        $user_list = stripslashes($_POST['user_list']);
        $user_cd_list = stripslashes($_POST['user_cd_list']);
        $user_name_list = stripslashes($_POST['user_name_list']);        
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_Client();
        $clients = $user_roles->get_all_clients();
        
        if ($clients === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $client_list = $clients[0];
        $client_cd_list = $clients[1];
        $client_name_list = $clients[2];
        
        $user_obj = new \my_inventory\Myi_User();
        $users = $user_obj->get_all_users();
        
        if ($users === false) {
            echo 'Error obtaining users lists<br/><br/>';
        }

        $user_list = $users[0];
        $user_cd_list = $users[1];
        $user_name_list = $users[2];  
    }
    
    if (isset($_POST['client-select']) && $_POST['client-select'] != 0
             && $_POST['user-select'] != 0 && isset($_POST['cb']) && wp_verify_nonce( $_POST['assign_roles_nonce'], 'assign_roles' )) {
            $user_roles = new \my_inventory\Myi_User_Roles();

            $cnt_succ = 0;
            $cnt_fail = 0;

            if ( isset( $_POST['cb'] ) ) {
                foreach( $_POST['cb'] as $role_id ) {
                    // if user has word press create_user rights, will treat as if myi_store_manager for this page
                    $result = $user_roles->add_role( $_POST['user-select'], 
                                                     $role_id, 
                                                     get_current_user_id(), 
                                                     $user_roles->get_role_ignore_client( get_current_user_id() ) .',' .(current_user_can( 'create_users' ) ?
                                                                                                                    '\'myi_store_manager\'' : ''),
                                                     $_POST['client-select']
                                                     ) ;

                    if ( $result ) {
                        $cnt_succ++;
                    } else {
                        $cnt_fail++;
                    }
                }
            }

            if ( $cnt_succ <= 0 ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Assigning failed... Ensure that roles for that client and user does not exists</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Roles assigned...' .$cnt_succ .' roles successfully assigned...' 
                        .( $cnt_fail > 0 ? $cnt_fail .' roles assigning failed...' : '') .'</span><br/>';
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="mod_roles" />
    <input type="hidden" name="user_list" id="user_list" value="<?php echo htmlspecialchars($user_list); ?>" />
    <input type="hidden" name="user_cd_list" id="user_cd_list" value="<?php echo htmlspecialchars($user_cd_list); ?>" />
    <input type="hidden" name="user_name_list" id="user_name_list" value="<?php echo htmlspecialchars($user_name_list); ?>" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'assign_roles', 'assign_roles_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
                <tr>
        				<td>User</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="user-select" name="user-select" data-live-search="true" 
                             >
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
        				<td>Client</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select" name="client-select" data-live-search="true" 
                             >
                            <option data-content="None Selected">0</option>
                        <?php 
                        $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $clients_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($clients_arr[1][$cnt]) .'   ' 
                                  .( $clients_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['client-select']) && $_POST['client-select'] == $clients_arr[0][$cnt] ? ' selected=selected ' : '' )
                                    .'>'
                                    .$clients_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>
                </tr>             
        		</tbody>
        	</table>
        </div>
            <p class="spacing-vert">&nbsp;</p>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr><td colspan=2><label class="header-highlight">User Access Roles</label></td>
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
