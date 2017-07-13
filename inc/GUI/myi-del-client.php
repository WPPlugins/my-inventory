<?php
namespace my_inventory\myi_del_client;

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
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'delete_inventory_master' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);           
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'delete_inventory_master', $client_cd_allowed );
        
        if ($clients === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $client_list = $clients[0];
        $client_cd_list = $clients[1];
        $client_name_list = $clients[2];       
    }
    
    if (isset($_POST['client-select']) && $_POST['client-select'] != 0 && $_POST['save_not_pressed'] != 'false'
         && wp_verify_nonce( $_POST['del_client_nonce'], 'del_client' ) ) {
            $client_rec = new \my_inventory\Myi_Client();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            $client_rec->get_client_by_id( $_POST['client-select'], $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ) );
            
            $result = $client_rec->del_client( get_current_user_id(),
                                               $user_roles->get_saved_role() );
            
                                               
            if ( !$result ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Deletion failed. Ensure that you have rights to delete the client and the client is not already deleted</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Client deleted...</span><br/>';
            }
    }    
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="delete_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="save_not_pressed" id="save_not_pressed" value="true" />
    </pre>
    <?php wp_nonce_field( 'del_client', 'del_client_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
        			<tr>
        				<td>Client</td>
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
                
                $chosen_client = new \my_inventory\Myi_Client();
                $result = $chosen_client->get_client_by_id( $_POST['client-select'], $user_role_for_client->get_role( get_current_user_id(), $_POST['client-select'] ) );
                
                if ( $result === false ) {
                    echo 'Error obtaining client particulars<br/><br/>';
                }
              
                ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Client Code :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_cd()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Client Name :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_name()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Remark :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_remark()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Address 1 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_address()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Address 2 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_address2()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Address 3 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_client_address3()); ?></label></pre></td>
                        </tr>  
                        <tr><td></td><td></td>
                        </tr>
                        <tr>
                            <td></td><td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-remove">    Delete</span>
                                        </button></td>
                        </tr>                        
                        </tbody>
                    </table>            
                </div>    
          <?php
          } // if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 )
          ?>
</div>      
</form>

<?php
} // user logged in
//wp_footer();
?>
