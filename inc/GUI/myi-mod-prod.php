<?php
namespace my_inventory\myi_mod_prod;

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

    // already retrieved the allowed client_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'mod_inventory_master' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);           
    } else { // get the allowed client_list
        $object = new \my_inventory\Myi_Product();
        $user_roles = new \my_inventory\Myi_User_Roles();
        
        $clients_list = $object->get_product_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($clients_list === false) {
            echo 'Error obtaining products list<br/><br/>';
        }
        
        $client_list = $clients_list[0];
        $client_cd_list = $clients_list[1];
        $client_name_list = $clients_list[2];          
    }
    
    if (isset($_POST['prod_name']) && $_POST['save_not_pressed'] != 'false' && wp_verify_nonce( $_POST['pict_upload_nonce'], 'pict_upload' )) {
            $client_rec = new \my_inventory\Myi_Product();
            $user_roles = new \my_inventory\Myi_User_Roles();

            // process the file image              
            if ($_FILES['pict_upload']['size'] > 0 && $_FILES['pict_upload']['error'] == 0) {
                $attachment_id = media_handle_upload( 'pict_upload', 0 );                
            } else {
                $logo_url = $_POST['old_pict_url'];
            }          
            
            if ( is_wp_error( $attachment_id ) ) {
                // There was an error uploading the image.
                
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Image upload failed. Ensure that you have rights to upload images.</span><br/>';
            } else {
                // The image was uploaded successfully!
                if ( isset( $attachment_id ) ) {
                    $logo_url = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
                }        

                // delete the old picture
                if ( isset($_POST['old_pict_url']) && trim($_POST['old_pict_url']) != '' && trim($_POST['old_pict_url']) != $logo_url ) {
                    wp_delete_attachment(\my_inventory\myi_get_image_id(stripslashes($_POST['old_pict_url'])));
                }
                
                $client_rec->get_product_by_id( $_POST['client-select'], $user_roles->get_role_ignore_client( get_current_user_id() ));                

                $result = $client_rec->mod_product( stripslashes($_POST['prod_name']), 
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
                                                    $user_roles->get_saved_role());
                                                   
                if ( !$result ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to modify the product.</span><br/>';
                } else {
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Product modified...</span><br/>';
                }
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#" enctype="multipart/form-data">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="mod_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />    
    <input type="hidden" name="save_not_pressed" id="save_not_pressed" value="true" />
    </pre>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
        			<tr>
        				<td>Product</td>
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
                
                $chosen_client = new \my_inventory\Myi_Product();
                $result = $chosen_client->get_product_by_id( $_POST['client-select'], $user_role_for_client->get_role_ignore_client( get_current_user_id() ) );
                
                if ( $result === false ) {
                    echo 'Error obtaining client particulars<br/><br/>';
                } ?>       
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields" for="prod_name">Product Name :</label></td>
                            <td><input type="text" class="form-control input_values" id="prod_name" name="prod_name" maxlength="100"
                                       value="<?php echo htmlspecialchars($chosen_client->get_prod_name());?>"></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="desc">Product Description :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="desc" name="desc" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_prod_desc());?></textarea></pre></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields" for="remark">Remark :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="remark" name="remark" maxlength="4000"
                            ><?php echo htmlspecialchars($chosen_client->get_prod_remark());?></textarea></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="dim">Dimension :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="dim" name="dim" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_prod_dimension());?></textarea></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Picture :</label></td>
                            <td><img src="<?php if ( $chosen_client->get_prod_img_url() === null || trim($chosen_client->get_prod_img_url()) == '' ) {
                                                        echo plugin_dir_url( __FILE__ ) .'../../assets/img/no-image.png';
                                                } else {
                                                        echo htmlspecialchars($chosen_client->get_prod_img_url());
                                                }?>" class="prod_img" />
                                <input type="hidden" name="old_pict_url" id="old_pict_url" value="<?php echo $chosen_client->get_prod_img_url(); ?>" />
                                <?php wp_nonce_field( 'pict_upload', 'pict_upload_nonce' ); ?>
                                <input type="file" accept="image/*" id="pict_upload" name="pict_upload" multiple="false"></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="lang">Logo Language :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="lang" name="lang" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_logo_lang());?></textarea></pre></td>
                        </tr>                        
                        <tr>
                            <td><label class="label_fields" for="add1">Additional Field 1 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add1" name="add1" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_add_field1());?></textarea></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="add2">Additional Field 2 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add2" name="add2" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_add_field2());?></textarea></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields" for="add3">Additional Field 3 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add3" name="add3" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_add_field3());?></textarea></pre></td>
                        </tr>         
                        <tr>
                            <td><label class="label_fields" for="add4">Additional Field 4 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add4" name="add4" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_add_field4());?></textarea></pre></td>
                        </tr>  
                        <tr>
                            <td><label class="label_fields" for="add5">Additional Field 5 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add5" name="add5" maxlength="300"
                            ><?php echo htmlspecialchars($chosen_client->get_add_field5());?></textarea></pre></td>
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
