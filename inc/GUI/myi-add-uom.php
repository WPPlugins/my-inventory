<?php
namespace my_inventory\myi_add_uom;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_uom.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  
    
    if (isset($_POST['uom_short']) && trim($_POST['uom_short'] != '') && trim($_POST['uom_shorts'] != '')
            && wp_verify_nonce( $_POST['add_uom_nonce'], 'add_uom' )) {
            $client_rec = new \my_inventory\Myi_UOM();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
                
                $result = $client_rec->add_uom( stripslashes($_POST['uom_short']),
                                                stripslashes($_POST['uom_shorts']),
                                                stripslashes($_POST['uom_full']),
                                                stripslashes($_POST['uom_fulls']),
                                                stripslashes($_POST['Remark']),
                                                get_current_user_id(),
                                                $user_roles->get_role_ignore_client( get_current_user_id() ));

                                                   
                if ( !$result ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to create UOM and that the UOM did not exist already.</span><br/>';
                } else {
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    UOM saved...</span><br/>';
                }                
    }
?>
<form id="my_form" name="my_form" method="post" action="#" enctype="multipart/form-data">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_inventory_master" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'add_uom', 'add_uom_nonce' ); ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields" for="uom_short">UOM Short form (Singular)(=1):</label></td>
                            <td><input type="text" class="form-control input_values" id="uom_short" name="uom_short" required maxlength="30"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="uom_shorts">UOM Short form (Plural)(>1) :</label></td>
                            <td><input type="text" class="form-control input_values" id="uom_shorts" name="uom_shorts" required maxlength="30"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="uom_full">UOM Full Name (Singular)(=1) :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="uom_full" name="uom_full" maxlength="300"></textarea></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields" for="uom_fulls">UOM Full Name (Plural)(>1) :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="uom_fulls" name="uom_fulls" maxlength="300"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="Remark">Remark :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="Remark" name="Remark" maxlength="4000"></textarea></td>
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
