<?php
namespace my_inventory\myi_add_prod;

include_once ABSPATH . 'wp-admin/includes/media.php';
include_once ABSPATH . 'wp-admin/includes/file.php';
include_once ABSPATH . 'wp-admin/includes/image.php';

require_once( plugin_dir_path( __FILE__ ) . '/../myi_product.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  
    
    if (isset($_POST['prod_cd']) && trim($_POST['prod_cd'] != '') && wp_verify_nonce( $_POST['pict_upload_nonce'], 'pict_upload' ) ) {
            $client_rec = new \my_inventory\Myi_Product();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            // process the file image              
            if ($_FILES['pict_upload']['size'] > 0 && $_FILES['pict_upload']['error'] == 0) {
                $attachment_id = media_handle_upload( 'pict_upload', 0 );                
            } else {
                $logo_url = '';
            }          
            
            if ( is_wp_error( $attachment_id ) ) {
                // There was an error uploading the image.
                
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Image upload failed. Ensure that you have rights to upload images.</span><br/>';
            } else {
                // The image was uploaded successfully!
                if ( isset( $attachment_id ) ) {
                    $logo_url = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
                }               
                
                
                $result = $client_rec->add_product( stripslashes($_POST['prod_cd']), 
                                                    stripslashes($_POST['prod_name']), 
                                                    stripslashes($_POST['desc']), 
                                                    stripslashes($_POST['dim']), 
                                                    $logo_url,
                                                    stripslashes($_POST['remark']), 
                                                    stripslashes($_POST['lang']), 
                                                    stripslashes($_POST['add1']),
                                                    stripslashes($_POST['add2']),
                                                    stripslashes($_POST['add3']),
                                                    stripslashes($_POST['add4']),
                                                    stripslashes($_POST['add5']),
                                                    get_current_user_id(),
                                                    $user_roles->get_role_ignore_client( get_current_user_id() ) );
                                                   
                if ( !$result ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to create product and that the product did not exist already.</span><br/>';
                } else {
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Product saved...</span><br/>';
                }                
            }            
    }
?>
<form id="my_form" name="my_form" method="post" action="#" enctype="multipart/form-data">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_inventory_master" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields" for="prod_cd">Product Code :</label></td>
                            <td><input type="text" class="form-control input_values" id="prod_cd" name="prod_cd" required maxlength="30"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="prod_name">Product Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="prod_name" name="prod_name" maxlength="100"></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="desc">Product Description :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="desc" name="desc" maxlength="300"></textarea></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields" for="remark">Remark :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="remark" name="remark" maxlength="4000"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="dim">Dimension :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="dim" name="dim" maxlength="300"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Picture :</label></td>
                            <td><?php wp_nonce_field( 'pict_upload', 'pict_upload_nonce' ); ?>
                                <input type="file" accept="image/*" id="pict_upload" name="pict_upload" multiple="false"></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="lang">Logo Language :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="lang" name="lang" maxlength="300"></textarea></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields" for="add1">Additional Field 1 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="add1" name="add1" maxlength="300"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="add2">Additional Field 2 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="add2" name="add2" maxlength="300"></textarea></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="add3">Additional Field 3 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="add3" name="add3" maxlength="300"></textarea></td>
                        </tr>         
                        <tr>
                            <td><label class="label_fields" for="add4">Additional Field 4 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="add4" name="add4" maxlength="300"></textarea></td>
                        </tr>  
                        <tr>
                            <td><label class="label_fields" for="add5">Additional Field 5 :</label></td>
                            <td><textarea class="form-control input_values" rows="5" id="add5" name="add5" maxlength="300"></textarea></td>
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
