<?php
namespace my_inventory\myi_link_cat;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_product_category.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_category.php');
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
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'create_inventory_master' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);        
        
        $cat_list = stripslashes($_POST['cat_list']);
        $cat_cd_list = stripslashes($_POST['cat_cd_list']);
        $cat_name_list = stripslashes($_POST['cat_name_list']);        
        
        $prod_list = stripslashes($_POST['prod_list']);
        $prod_cd_list = stripslashes($_POST['prod_cd_list']);
        $prod_name_list = stripslashes($_POST['prod_name_list']);           
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'create_inventory_master', $client_cd_allowed );
        
        if ($clients === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $client_list = $clients[0];
        $client_cd_list = $clients[1];
        $client_name_list = $clients[2];
        
        $categories = new \my_inventory\Myi_Category();
        $cats = $categories->get_categories_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($cats === false) {
            echo 'Error obtaining categories lists<br/><br/>';
        }
        
        $cat_list = $cats[0];
        $cat_cd_list = $cats[1];
        $cat_name_list = $cats[2];    

        $products = new \my_inventory\Myi_Product();
        $prods = $products->get_product_list( $user_roles->get_saved_role() );
        
        if ($prods === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $prod_list = $prods[0];
        $prod_cd_list = $prods[1];
        $prod_name_list = $prods[2];           
    }
    
    if (isset($_POST['cat-select']) && trim($_POST['client-select'] != 0) && trim($_POST['cat-select'] != 0) && trim($_POST['prod-select'] != 0)
         && wp_verify_nonce( $_POST['link_cat_nonce'], 'link_cat' )) {
            $client_rec = new \my_inventory\Myi_Prod_Cat();
            $user_roles = new \my_inventory\Myi_User_Roles();
            
            $result = $client_rec->link_prod_to_cat (   $_POST['prod-select'],
                                                        $_POST['cat-select'],
                                                        $_POST['client-select'],
                                                        get_current_user_id(),
                                                        $user_roles->get_role( get_current_user_id(), $_POST['client-select']) );

            if ( !$result ) {
                echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Saving failed. Ensure that you have rights to link products to category and such links do not exist already.</span><br/>';
            } else {
                echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Link saved...</span><br/>';
            }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="cat_list" id="cat_list" value="<?php echo htmlspecialchars($cat_list); ?>" />
    <input type="hidden" name="cat_cd_list" id="cat_cd_list" value="<?php echo htmlspecialchars($cat_cd_list); ?>" />
    <input type="hidden" name="cat_name_list" id="cat_name_list" value="<?php echo htmlspecialchars($cat_name_list); ?>" />   
    <input type="hidden" name="prod_list" id="prod_list" value="<?php echo htmlspecialchars($prod_list); ?>" />
    <input type="hidden" name="prod_cd_list" id="prod_cd_list" value="<?php echo htmlspecialchars($prod_cd_list); ?>" />
    <input type="hidden" name="prod_name_list" id="cat_name_list" value="<?php echo htmlspecialchars($prod_name_list); ?>" />     
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'link_cat', 'link_cat_nonce' ); ?>
             <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
                <tr>
        				<td>Client</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select" name="client-select" data-live-search="true" 
                             data-rule-min="1" data-msg-min="Please choose a client">
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
                <tr>
        				<td>Category</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="cat-select" name="cat-select" data-live-search="true" 
                             data-rule-min="1" data-msg-min="Please choose a category">
                            <option data-content="None Selected">0</option>
                        <?php 
                        $cat_arr = array( explode( ',', $cat_list ), explode( '~|`', $cat_cd_list), explode( '~|`', $cat_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $cat_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($cat_arr[1][$cnt]) .'   ' 
                                  .( $cat_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($cat_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['cat-select']) && $_POST['cat-select'] == $cat_arr[0][$cnt] ? ' selected=selected ' : '' )
                                    .'>'
                                    .$cat_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>
                </tr>
                <tr>
        				<td>Product</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="prod-select" name="prod-select" data-live-search="true" 
                             data-rule-min="1" data-msg-min="Please choose a product">
                            <option data-content="None Selected">0</option>
                        <?php 
                        $prod_arr = array( explode( ',', $prod_list ), explode( '~|`', $prod_cd_list), explode( '~|`', $prod_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $prod_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($prod_arr[1][$cnt]) .'   ' 
                                  .( $prod_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($prod_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['prod-select']) && $_POST['prod-select'] == $prod_arr[0][$cnt] ? ' selected=selected ' : '' )
                                    .'>'
                                    .$prod_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>                           
                </tr>                
                <tr>
                    <td colspan=100%></td>
                </tr>
                <tr>
                    <td></td>
                    <td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save">    Save</span>
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
