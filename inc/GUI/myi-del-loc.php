<?php
namespace my_inventory\myi_del_loc;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_location.php');

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
        $object = new \my_inventory\Myi_Location();
        $user_roles = new \my_inventory\Myi_User_Roles();
        
        $clients_list = $object->get_location_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($clients_list === false) {
            echo 'Error obtaining locations list<br/><br/>';
        }
        
        $client_list = $clients_list[0];
        $client_cd_list = $clients_list[1];
        $client_name_list = $clients_list[2];    
    }
    
    if (isset($_POST['client-select']) && $_POST['client-select'] != 0 && $_POST['save_not_pressed'] != 'false'
            && wp_verify_nonce( $_POST['del_loc_nonce'], 'del_loc' ) ) {
            $client_rec = new \my_inventory\Myi_Location();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            $client_rec->get_location_by_id( $_POST['client-select'], $user_roles->get_role_ignore_client( get_current_user_id() ));
            
            $result = $client_rec->del_location( get_current_user_id(),
                                                 $user_roles->get_saved_role() );
            
                                               
            if ( !$result ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Deletion failed. Ensure that you have rights to delete the location and the location is not already deleted</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Location deleted...</span><br/>';
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
    <?php wp_nonce_field( 'del_loc', 'del_loc_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
        			<tr>
        				<td>Location</td>
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
                
                $chosen_client = new \my_inventory\Myi_Location();
                $result = $chosen_client->get_location_by_id( $_POST['client-select'], $user_role_for_client->get_role_ignore_client( get_current_user_id() ) );
                
                if ( $result === false ) {
                    echo 'Error obtaining location information<br/><br/>';
                }
              
                ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Location Code :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_location_cd()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Location Name :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_location_name()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Location Description :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_location_desc()); ?></label></pre></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields">Remark :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_location_remark()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Additional Field 1 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_add_field1()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Additional Field 2 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_add_field2()); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Additional Field 3 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_add_field3()); ?></label></pre></td>
                        </tr>         
                        <tr>
                            <td><label class="label_fields">Additional Field 4 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_add_field4()); ?></label></pre></td>
                        </tr>  
                        <tr>
                            <td><label class="label_fields">Additional Field 5 :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_add_field5()); ?></label></pre></td>
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
