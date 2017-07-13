<?php
namespace my_inventory\myi_view_cat;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_category.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {

    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }
    
    // already retrieved the allowed prod_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'view_inventory_master' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);           
    } else { // get the allowed client_list
        $object = new \my_inventory\Myi_Category();
        $user_roles = new \my_inventory\Myi_User_Roles();
        
        $clients_list = $object->get_categories_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($clients_list === false) {
            echo 'Error obtaining categories list<br/><br/>';
        }
        
        $client_list = $clients_list[0];
        $client_cd_list = $clients_list[1];
        $client_name_list = $clients_list[2];       
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="view_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    </pre>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
        			<tr>
        				<td>Category</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select" name="client-select" data-live-search="true" 
                             onchange="this.form.submit();">
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
                
                $chosen_client = new \my_inventory\Myi_Category();
                $result = $chosen_client->get_category_by_id( $_POST['client-select'], $user_role_for_client->get_role_ignore_client( get_current_user_id() ) );
                
                if ( $result === false ) {
                    echo 'Error obtaining category information<br/><br/>';
                }
              
                ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Category Code :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_cat_cd()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Category Name :</label></td>
                            <td><label class="label_values"><?php echo htmlspecialchars($chosen_client->get_cat_name()); ?></label></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">Picture :</label></td>
                            <td><img src="<?php if ( $chosen_client->get_cat_img_url() === null || trim($chosen_client->get_cat_img_url()) == '' ) {
                                                        echo plugin_dir_url( __FILE__ ) .'../../assets/img/no-image.png';
                                                } else {
                                                        echo $chosen_client->get_cat_img_url();
                                                }?>" class="prod_img"/>
                                                </td>                            
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
