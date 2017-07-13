<?php
//require( dirname(__FILE__) . '/../../../../../wp-load.php' );
//wp_head();
require_once(__DIR__ . '/../myi_uom_record.php');
require_once(__DIR__ . '/../myi_product.php');

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
        
        $prod_list = stripslashes($_POST['prod_list']);
        $prod_cd_list = stripslashes($_POST['prod_cd_list']);
        $prod_name_list = stripslashes($_POST['prod_name_list']);        
    } else { // get the allowed client_list
        $user_roles = new Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'view_inventory_master', $client_cd_allowed );
        
        if ($clients === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $client_list = $clients[0];
        $client_cd_list = $clients[1];
        $client_name_list = $clients[2];
        
        $products = new Myi_Product();
        $prods = $products->get_product_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($prods === false) {
            echo 'Error obtaining products list<br/><br/>';
        }
        
        $prod_list = $prods[0];
        $prod_cd_list = $prods[1];
        $prod_name_list = $prods[2];                
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="view_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="prod_list" id="prod_list" value="<?php echo htmlspecialchars($prod_list); ?>" />
    <input type="hidden" name="prod_cd_list" id="prod_cd_list" value="<?php echo htmlspecialchars($prod_cd_list); ?>" />
    <input type="hidden" name="prod_name_list" id="prod_name_list" value="<?php echo htmlspecialchars($prod_name_list); ?>" />
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
        				<td>Product</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="prod-select" name="prod-select" data-live-search="true" 
                             onchange="this.form.submit();">
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
        		</tbody>
        	</table>
        </div>
            <p class="spacing-vert">&nbsp;</p>
          <?php 
          if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 && $_POST['prod-select'] != 0 ) {
                $user_role_for_client = new Myi_User_Roles();

                $prod_uom = new Myi_UOM_Record();
                $prod_uom_recs = $prod_uom->get_record_by_cli_prd ( $_POST['client-select'],
                                                                    $_POST['prod-select'],
                                                                    $user_role_for_client->get_role( get_current_user_id(), $_POST['client-select'] ) );

                if ( $prod_uom_recs === false ) {
                    echo 'Error obtaining products UOMs<br/><br/>';
                }

                for ($cnt=0; $cnt < count( $prod_uom_recs); $cnt++) {
                    $uoms = $prod_uom->get_uoms_in_arr_with_name( $prod_uom_recs[$cnt] );
                ?>
                <p class="spacing-vert">&nbsp;</p>
                <div id="no-more-tables<?php echo $cnt; ?>">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Description :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($prod_uom_recs[$cnt]->desc); ?></label></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">UOMs hierarchy :</label></td>
                            <td><div class="uom-levels">
                                <?php
                                        for ( $c = $prod_uom->get_first_uom_lvl(); $c <= $prod_uom->get_last_uom_lvl(); $c++ ) {
                                                // UOM label
                                                echo '<div class="box ';
                                                
                                                if ( $uoms[$c + $prod_uom->get_last_uom_lvl()][0] === null ) {
                                                    echo ' no-display';
                                                }
                                                
                                                if ( $c == $prod_uom_recs[$cnt]->uom_default_level ) {
                                                    echo ' default';
                                                }
                                                echo '"><label class="label_values">' .$uoms[$c + $prod_uom->get_last_uom_lvl()][4] .'</label></div><br/>';
                                                
                                                // UOM next level qty
                                                echo '<span class="glyphicon glyphicon-arrow-up ';
                                                if ( $uoms[$c + $prod_uom->get_last_uom_lvl()][0] === null || $uoms[$c + $prod_uom->get_last_uom_lvl()][2] == 0 ) {
                                                    echo 'no-display';
                                                }                                                
                                                
                                                echo '"> (X ' .$uoms[$c + $prod_uom->get_last_uom_lvl()][2] .')</span>';
                                        }
                                ?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>            
                </div>    
          <?php } // for ?>
          <?php
          } // if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 )
          ?>
</div>      
</form>

<?php
} // user logged in
//wp_footer();
?>
