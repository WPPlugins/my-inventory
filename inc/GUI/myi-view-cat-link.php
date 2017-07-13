<?php
namespace my_inventory\myi_view_cat_link;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_product_category.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_category.php');

if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {

    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }
    
    // already retrieved the allowed client_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'view_inventory_master' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);        
        
        $cat_list = stripslashes($_POST['cat_list']);
        $cat_cd_list = stripslashes($_POST['cat_cd_list']);
        $cat_name_list = stripslashes($_POST['cat_name_list']);        
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'view_inventory_master', $client_cd_allowed );
        
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
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="view_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="cat_list" id="cat_list" value="<?php echo htmlspecialchars($cat_list); ?>" />
    <input type="hidden" name="cat_cd_list" id="cat_cd_list" value="<?php echo htmlspecialchars($cat_cd_list); ?>" />
    <input type="hidden" name="cat_name_list" id="cat_name_list" value="<?php echo htmlspecialchars($cat_name_list); ?>" />
    </pre>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
       			<tr>
        				<td>Client</td>
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
                <tr>
        				<td>Category</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="cat-select" name="cat-select" data-live-search="true" 
                             onchange="this.form.submit();">
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
        		</tbody>
        	</table>
        </div>
            <p class="spacing-vert">&nbsp;</p>
          <?php 
          
          if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 && $_POST['cat-select'] != 0 ) {
                $user_role_for_client = new \my_inventory\Myi_User_Roles();
                
                $prod_cat = new \my_inventory\Myi_Prod_Cat();
                $prod_cat_recs = $prod_cat->get_prod_in_cat ( $_POST['cat-select'], 
                                                              $_POST['client-select'], 
                                                              $user_role_for_client->get_role( get_current_user_id(), $_POST['client-select'] ) );
       
       
                if ( $prod_cat_recs === false ) {
                    echo 'Error obtaining products categories linkage<br/><br/>';
                }
              
                ?>
                <div id="no-more-tables2">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        <tr>
                            <th></th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $prod_cat_recs as $row ) { ?>                            
                        <tr>
                            <td><span class="glyphicon glyphicon-level-up level-two"></span></td>
                            <td><label class="mobile-header">Product Code : </label>
                                <label class="label_values"><?php echo htmlspecialchars($row->prod_cd); ?></label></td>
                            <td><label class="mobile-header">Product Name : </label>
                                <label class="label_values"><?php echo htmlspecialchars($row->prod_name); ?></label></td>
                        </tr>
                        <?php } // for each ?>
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
