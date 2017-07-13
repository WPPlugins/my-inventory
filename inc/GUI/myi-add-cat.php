<?php
namespace my_inventory\myi_add_cat;

include_once ABSPATH . 'wp-admin/includes/media.php';
include_once ABSPATH . 'wp-admin/includes/file.php';
include_once ABSPATH . 'wp-admin/includes/image.php';

require_once( plugin_dir_path( __FILE__ ) . '/../myi_category.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {
    
    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }  
    
    if (isset($_POST['cat_cd']) && trim($_POST['cat_cd'] != '') && wp_verify_nonce( $_POST['pict_upload_nonce'], 'pict_upload' ) ) {
            $client_rec = new \my_inventory\Myi_Category();
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
                
                
                $result = $client_rec->add_category( stripslashes($_POST['cat_cd']),
                                                     stripslashes($_POST['cat_name']),
                                                     $logo_url,
                                                     get_current_user_id(),
                                                     $user_roles->get_role_ignore_client( get_current_user_id() ) );
                                                   
                if ( !$result ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to create category and that the category did not exist already.</span><br/>';
                } else {
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Category saved...</span><br/>';
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
                            <td><label class="label_fields" for="prod_cd">Category Code :</label></td>
                            <td><input type="text" class="form-control input_values" id="cat_cd" name="cat_cd" required maxlength="30"><span class="glyphicon glyphicon-asterisk required">Required</span></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="prod_name">Category Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="cat_name" name="cat_name" maxlength="300"></td>
                        </tr>                      
                        <tr>
                            <td><label class="label_fields">Picture :</label></td>
                            <td><?php wp_nonce_field( 'pict_upload', 'pict_upload_nonce' ); ?>
                                <input type="file" accept="image/*" id="pict_upload" name="pict_upload" multiple="false"></td>
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
