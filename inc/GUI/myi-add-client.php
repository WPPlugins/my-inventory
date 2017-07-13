<?php
namespace my_inventory\myi_add_client;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_client.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  
    
    if (isset($_POST['client_cd']) && trim($_POST['client_cd'] != '') && wp_verify_nonce( $_POST['add_client_nonce'], 'add_client' ) ) {
            $client_rec = new \my_inventory\Myi_Client();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            $result = $client_rec->add_client( stripslashes($_POST['client_cd']),
                                               stripslashes($_POST['client_name']),
                                               stripslashes($_POST['remark']), 
                                               stripslashes($_POST['addr1']),
                                               stripslashes($_POST['addr2']),
                                               stripslashes($_POST['addr3']),
                                               get_current_user_id(),
                                               $user_roles->get_role( get_current_user_id() ) );
                                               
            if ( !$result ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to create client and that the client did not exist already.</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Client saved...</span><br/>';
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_inventory_master" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'add_client', 'add_client_nonce' ); ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields" for="client_cd">Client Code :</label></td>
                            <td><input type="text" class="form-control input_values" id="client_cd" name="client_cd" required maxlength="30"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="client_name">Client Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="client_name" name="client_name" maxlength="300"></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="remark">Remark :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="remark" name="remark" maxlength="4000"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="addr1">Address 1 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="addr1" name="addr1" maxlength="4000"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="addr2">Address 2 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="addr2" name="addr2" maxlength="4000"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="addr3">Address 3 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="addr3" name="addr3" maxlength="4000"></textarea></td>
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
</div>                
</form>

<?php
} //user logged in
//wp_footer();
?>
