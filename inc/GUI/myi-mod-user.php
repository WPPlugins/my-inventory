<?php
namespace my_inventory\myi_mod_user;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_product.php');

// there is no admin_dir function. Also from what I gathered from the web, we are not supposed to change wp-admin as it will break many things.
// the post i read the information is around April 2016. 
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

    // already retrieved the allowed client_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'mod_user' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);           
    } else { // get the allowed client_list
        $object = new \my_inventory\Myi_User();
        $user_roles = new \my_inventory\Myi_User_Roles();
        
        $clients_list = $object->get_all_users();
        
        if ($clients_list === false) {
            echo 'Error obtaining users list<br/><br/>';
        }
        
        $client_list = $clients_list[0];
        $client_cd_list = $clients_list[1];
        $client_name_list = $clients_list[2];          
    }
    
    if ( isset($_POST['user_name']) && trim($_POST['user_name']) != '' && trim($_POST['email']) != ''  
         && $_POST['save_not_pressed'] != 'false' && wp_verify_nonce( $_POST['mod_user_nonce'], 'mod_user' )) {
            $client_rec = new \my_inventory\Myi_User();
            $user_roles = new \my_inventory\Myi_User_Roles();

            $result = $client_rec->mod_user (   $_POST['client-select'],
                                                stripslashes($_POST['pass']),
                                                stripslashes($_POST['email']),
                                                stripslashes($_POST['user_name']),
                                                stripslashes($_POST['desc']),
                                                $user_roles->get_role_ignore_client( get_current_user_id() ).',' .(current_user_can( 'create_users' ) ?
                                                                                                                    '\'myi_store_manager\'' : ''));


                if ( !$result ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to modify the user.</span><br/>';
                } else {
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    User modified...</span><br/>';
                }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="mod_user" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />    
    <input type="hidden" name="save_not_pressed" id="save_not_pressed" value="true" />
    </pre>
    <?php wp_nonce_field( 'mod_user', 'mod_user_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
        			<tr>
        				<td>User</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select" name="client-select" data-live-search="true" 
                             onchange="getElementById('save_not_pressed').value = 'false'; this.form.submit();">
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
        <?php
          if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 ) {
                $user_role_for_client = new \my_inventory\Myi_User_Roles();
                
                $chosen_client = new \my_inventory\Myi_User();
                $result = $chosen_client->get_all_users_by_id( $_POST['client-select'] );

                if ( $result === false ) {
                    echo 'Error obtaining user particulars<br/><br/>';
                } ?>       
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Login ID :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_user_login()); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="user_name">Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="user_name" name="user_name" maxlength="250" required
                                       value="<?php echo htmlspecialchars($chosen_client->get_display_name());?>"><span class="glyphicon glyphicon-asterisk required">Required</span>
                            </td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="pass">Change Password To:</label></td>
                            <td><input type="text" class="form-control input_values" id="pass" name="pass" maxlength="255" data-rule-minlength="8"
                                    value=""></input></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="email">Email :</label></td>
                            <td><input type="text" class="form-control input_values" id="email" name="email" required maxlength="100" data-rule-email="true"
                                 value="<?php echo htmlspecialchars($chosen_client->get_user_email());?>"><span class="glyphicon glyphicon-asterisk required">Required</span></input></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="desc">Description :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="desc" name="desc" maxlength="4000"><?php 
                                    echo htmlspecialchars($chosen_client->get_description());?></textarea></pre></td>
                        </tr>  
                        <tr><td></td><td></td>
                        </tr>
                        <tr>
                            <td></td><td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save">    Save</span>
                                        </button></td>
                        </tr>
                        </tbody>
                    </table>            
                </div>   
<?php       } //if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 )
?>                
</div>                
</form>

<?php
} //user logged in
//wp_footer();
?>
