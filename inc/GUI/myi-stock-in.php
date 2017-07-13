<?php
namespace my_inventory\myi_stock_in;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_uom_record.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_inventory.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_product.php');
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
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'stock_mod_inventory' ) {
        $client_list = stripslashes($_POST['client_list']);
        $client_cd_list = stripslashes($_POST['client_cd_list']);
        $client_name_list = stripslashes($_POST['client_name_list']);        
        
        $prod_list = stripslashes($_POST['prod_list']);
        $prod_cd_list = stripslashes($_POST['prod_cd_list']);
        $prod_name_list = stripslashes($_POST['prod_name_list']);

        $loc_list = stripslashes($_POST['loc_list']);
        $loc_cd_list = stripslashes($_POST['loc_cd_list']);
        $loc_name_list = stripslashes($_POST['loc_name_list']);
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'stock_mod_inventory', $client_cd_allowed );
        
        if ($clients === false) {
            echo 'Error obtaining clients lists<br/><br/>';
        }
        
        $client_list = $clients[0];
        $client_cd_list = $clients[1];
        $client_name_list = $clients[2];
        
        $products = new \my_inventory\Myi_Product();
        $prods = $products->get_product_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($prods === false) {
            echo 'Error obtaining products list<br/><br/>';
        }
        
        $prod_list = $prods[0];
        $prod_cd_list = $prods[1];
        $prod_name_list = $prods[2];

        $locations = new \my_inventory\Myi_Location();
        $locs = $locations->get_location_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($locs === false) {
            echo 'Error obtaining location list<br/><br/>';
        }
        
        $loc_list = $locs[0];
        $loc_cd_list = $locs[1];
        $loc_name_list = $locs[2];
    }
    
    if (isset($_POST['submit_btn_id']) && $_POST['save_not_pressed'] != 'false' && wp_verify_nonce( $_POST['stock_in_nonce'], 'stock_in' ) ) {
        // break down the values of the submit button
        $impt_str = substr(stripslashes($_POST['submit_btn_id']), 9);
        $expl = explode( '_', $impt_str );
        
        $action = $expl[0];
        $id = $expl[1];
        
        // get all uoms
        $uom_arr = array();
                
        $uom_rec = new \my_inventory\Myi_UOM_Record();
        $user_roles = new \my_inventory\Myi_User_Roles();
                
        for ($cnt = $uom_rec->get_first_uom_lvl(); $cnt <= $uom_rec->get_last_uom_lvl(); $cnt++){
            array_push($uom_arr, array($_POST['uom_id_'.$id .'_' .$cnt], 
                       ( $action == 'out' ? $_POST['qty'.$id .'_' .$cnt] * -1 : $_POST['qty'.$id .'_' .$cnt] )));
        }
        
        $inv = new \my_inventory\Myi_Inventory();
        
        $result = $inv->add_stock_by_prod_uom_id ( $id,
                                                   stripslashes($_POST['loc-select' .$id]),
                                                   stripslashes($_POST['client-meant-select' .$id]),
                                                   get_current_user_id(), 
                                                   $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ),
                                                   stripslashes($_POST['job_no' .$id]),
                                                   stripslashes($_POST['remark' .$id]),
                                                   stripslashes($_POST['add1-' .$id]),
                                                   stripslashes($_POST['add2-' .$id]),
                                                   stripslashes($_POST['add3-' .$id]),
                                                   stripslashes($_POST['add4-' .$id]),
                                                   stripslashes($_POST['add5-' .$id]),
                                                   $uom_arr);

        if ( !$result ) {
            echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Stock In/Out failed. Ensure that you have rights to do the stock in/out and there is sufficient quantity to stock out</span><br/>';
        } else {
            echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Stock In/Out successful...</span><br/>';
        }
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="stock_mod_inventory" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="prod_list" id="prod_list" value="<?php echo htmlspecialchars($prod_list); ?>" />
    <input type="hidden" name="prod_cd_list" id="prod_cd_list" value="<?php echo htmlspecialchars($prod_cd_list); ?>" />
    <input type="hidden" name="prod_name_list" id="prod_name_list" value="<?php echo htmlspecialchars($prod_name_list); ?>" />
    <input type="hidden" name="loc_list" id="loc_list" value="<?php echo htmlspecialchars($loc_list); ?>" />
    <input type="hidden" name="loc_cd_list" id="loc_cd_list" value="<?php echo htmlspecialchars($loc_cd_list); ?>" />
    <input type="hidden" name="loc_name_list" id="loc_name_list" value="<?php echo htmlspecialchars($loc_name_list); ?>" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    <input type="hidden" name="submit_btn_id" id="submit_btn_id" value="" />
    <input type="hidden" name="save_not_pressed" id="save_not_pressed" value="true" />
    </pre>
    <?php wp_nonce_field( 'stock_in', 'stock_in_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
       			<tr>
        				<td>Client</td>
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
                <tr>
        				<td>Product</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="prod-select" name="prod-select" data-live-search="true" 
                             onchange="getElementById('save_not_pressed').value = 'false'; this.form.submit();">
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
                $user_role_for_client = new \my_inventory\Myi_User_Roles();

                $prod_uom = new \my_inventory\Myi_UOM_Record();
                $prod_uom_recs = $prod_uom->get_record_by_cli_prd ( $_POST['client-select'],
                                                                    $_POST['prod-select'],
                                                                    $user_role_for_client->get_role( get_current_user_id(), $_POST['client-select'] ),
                                                                    'stock_mod_inventory');

                if ( $prod_uom_recs === false ) {
                    echo 'Error obtaining products UOMs<br/><br/>';
                }

                $inv = new \my_inventory\Myi_Inventory();

                for ($cnt=0; $cnt < count( $prod_uom_recs); $cnt++) {
                    $uoms = $prod_uom->get_uoms_in_arr_with_name( $prod_uom_recs[$cnt] );
                    $qty = $inv->get_stock_count_by_prod_uom_id ( $prod_uom_recs[$cnt]->id, 
                                                                  $user_role_for_client->get_saved_role() );
                    $display_qty = $inv->get_stock_count_for_display (  $prod_uom_recs[$cnt]->id, 
                                                                        $qty[0] );
                ?>
                <p class="spacing-vert">&nbsp;</p>
                <div id="no-more-tables<?php echo $cnt; ?>">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        <tr>
                            <th colspan=2>Stocks Level</th>
                            <th colspan=2>Stock In/Out</th>
                        </tr>                        
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="mobile-header header-highlight">Stocks Level </label><label class="label_fields">Description :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($prod_uom_recs[$cnt]->desc); ?></label></pre></td>
                            <td colspan=2></td>
                        </tr>
                        <tr>
                            <td rowspan=11><label class="label_fields">UOMs hierarchy :</label></td>
                            <td rowspan=11><div class="uom-levels">
                                <?php
                                        // show total quantity in smallest uom
                                        echo '<div class="' .( $qty[0] > 0 ? 'smallest_uom_qty' : '' ) .'"><label class="label_values ' 
                                             .( $qty[0] > 0 ? 'smallest_uom_qty' : '' ) .'">Total : &nbsp;&nbsp;&nbsp;<b>'
                                             .$qty[0] .'</b>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .$qty[2]  .'</label></div>';
                                                     
                                        for ( $c = $prod_uom->get_first_uom_lvl(); $c <= $prod_uom->get_last_uom_lvl(); $c++ ) {
                                                // UOM label
                                                echo '<div class="box ';
                                                
                                                if ( $uoms[$c + $prod_uom->get_last_uom_lvl()][0] === null ) {
                                                    echo ' no-display';
                                                }
                                                
                                                if ( $c == $prod_uom_recs[$cnt]->uom_default_level ) {
                                                    echo ' default';
                                                }
                                                
                                                for ($ct = 0; $ct < count($display_qty); $ct++) {
                                                    if ($display_qty[$ct][3] == $c) { // same uom lvl
                                                        if ($display_qty[$ct][0] > 0) {
                                                            echo '"><label class="label_values highlight_value">' .$display_qty[$ct][0] .'</label value="';
                                                        } else {
                                                            echo '"><label class="label_values">' .$display_qty[$ct][0] .'</label value="';
                                                        }
                                                        
                                                        break;
                                                    }
                                                }
                                                echo '"><label class="label_values" >&nbsp;' .$display_qty[$ct][1] .'</label></div><br/>';
                                                
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
                            <td><label class="mobile-header header-highlight">Stocks In/Out </label><label class="label_fields" for="loc_select<?php echo $prod_uom_recs[$cnt]->id;?>">Location :</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="loc-select<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="loc-select<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true">
                            <option data-content="None Selected">0</option>
                            <?php 
                            $loc_arr = array( explode( ',', $loc_list ), explode( '~|`', $loc_cd_list), explode( '~|`', $loc_name_list ) );                       

                            for ( $ct = 0; $ct < count( $loc_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($loc_arr[1][$ct]) .'   ' 
                                      .( $loc_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($loc_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['loc-select']) && $_POST['loc-select'] == $loc_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$loc_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>">Stocks meant for :</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true" 
                                >
                            <option data-content="None Selected">0</option>
                            <?php 
                            $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                            
                            for ( $ct = 0; $ct < count( $clients_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($clients_arr[1][$ct]) .'   ' 
                                      .( $clients_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['client-meant-select']) && $_POST['client-meant-select'] == $clients_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$clients_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="job_no<?php echo $prod_uom_recs[$cnt]->id;?>">Job No :</label></td>
                            <td><input type="text" class="form-control input_values" id="job_no<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                 name="job_no<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="remark<?php echo $prod_uom_recs[$cnt]->id;?>">Remarks :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="remark<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="remark<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="4000"
                            ></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add1-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 1 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add1-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add1-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add2-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 2 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add2-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add2-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ></textarea></pre></td>
                        </tr> 
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add3-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 3 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add3-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add3-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ></textarea></pre></td>
                        </tr> 
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add4-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 4 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add4-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add4-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add5-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 5 :</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add5-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add5-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields">UOM Hierarchy :</label></td>
                            <td><?php   for ( $c = $prod_uom->get_first_uom_lvl(); $c <= $prod_uom->get_last_uom_lvl(); $c++ ) {
                                                // UOM label
                                                echo '<div class="box ';
                                                
                                                if ( $uoms[$c + $prod_uom->get_last_uom_lvl()][0] === null ) {
                                                    echo ' no-display';
                                                }
                                                
                                                if ( $c == $prod_uom_recs[$cnt]->uom_default_level ) {
                                                    echo ' default';
                                                }
                                                
                                                echo '"><input type="text" class="form-control input_values" id="qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" 
                                                        name="qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" data-rule-number=true data-rule-min="0"> <label class="label_values">&nbsp;' .$uoms[$c + $prod_uom->get_last_uom_lvl()][4] .'</label></div><br/>';
                                                        
                                                // hidden uom_id
                                                echo '<input type="text" class="no-display" id="uom_id_' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'"
                                                       name="uom_id_' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" 
                                                       value="' .$uoms[$c + $prod_uom->get_last_uom_lvl()][0] .'"></input>';
                                        } ?>
                            </td>
                        </tr>   
                        <tr>
                            <td colspan=2><button type="submit" id="save_btn_in_<?php echo $prod_uom_recs[$cnt]->id; ?>" 
                                           class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign">    Stock In</span>
                                        </button><br/><br/>
                                        <button type="submit" id="save_btn_out_<?php echo $prod_uom_recs[$cnt]->id; ?>" 
                                           class="btn btn-primary"><span class="glyphicon glyphicon-minus-sign">    Stock Out</span>
                                        </button></td>
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
