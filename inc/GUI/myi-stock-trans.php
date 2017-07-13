<?php
namespace my_inventory\myi_stock_trans;

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
        $id_to = $_POST['id_to'.$id];

        // get all uoms
        $uom_arr = array();
                
        $uom_rec = new \my_inventory\Myi_UOM_Record();
        $user_roles = new \my_inventory\Myi_User_Roles();
                
        for ($cnt = $uom_rec->get_first_uom_lvl(); $cnt <= $uom_rec->get_last_uom_lvl(); $cnt++){
            array_push($uom_arr, array($_POST['uom_id_'.$id .'_' .$cnt], 
                       $_POST['qty'.$id .'_' .$cnt] * -1 ));
        }
        
        $inv = new \my_inventory\Myi_Inventory();
        
        $result = $inv->transfer_stock( $id, 
                                        $id_to,
                                        stripslashes($_POST['loc-select' .$id]),
                                        stripslashes($_POST['loc-select-to' .$id]),
                                        stripslashes($_POST['client-meant-select' .$id]),
                                        stripslashes($_POST['client-meant-select-to' .$id]),
                                        get_current_user_id(), 
                                        $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ),
                                        $user_roles->get_role( get_current_user_id(), $_POST['client-select-to' .$id] ),
                                        stripslashes($_POST['job_no' .$id]),
                                        stripslashes($_POST['remark' .$id]),
                                        stripslashes($_POST['add1-' .$id]),
                                        stripslashes($_POST['add2-' .$id]),
                                        stripslashes($_POST['add3-' .$id]),
                                        stripslashes($_POST['add4-' .$id]),
                                        stripslashes($_POST['add5-' .$id]),                                        
                                        stripslashes($_POST['job_no_to' .$id]),
                                        stripslashes($_POST['remark_to' .$id]),
                                        stripslashes($_POST['add1-to-' .$id]),
                                        stripslashes($_POST['add2-to-' .$id]),
                                        stripslashes($_POST['add3-to-' .$id]),
                                        stripslashes($_POST['add4-to-' .$id]),
                                        stripslashes($_POST['add5-to-' .$id]),  
                                        $uom_arr);

        if ( !$result ) {
            echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Transfer failed. Ensure that you have rights and both transfer from and to have the same smallest UOM</span><br/>';
        } else {
            echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    Transfer successful...</span><br/>';
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
                $smallest_uom_id = array();

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
                            <th colspan=2>Transfer From</th>
                            <th colspan=2>Transfer To</th>
                        </tr>                        
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="mobile-header header-highlight">Stocks Level </label><label class="label_fields">Description :</label></td>
                            <td><pre><label class="label_values"><?php echo htmlspecialchars($prod_uom_recs[$cnt]->desc); ?></label></pre></td>
                            <td colspan=2></td>
                        </tr>
                        <tr>
                            <td rowspan=12><label class="label_fields">UOMs hierarchy :</label></td>
                            <td rowspan=12><div class="uom-levels">
                                <?php   
                                        $smallest_uom_id[$cnt] = $qty[1];
                                        
                                        // show total quantity in smallest uom
                                        echo '<div class="' .( $qty[0] > 0 ? 'smallest_uom_qty' : '' ) .'"><label class="label_values ' 
                                             .( $qty[0] > 0 ? 'smallest_uom_qty' : '' ) .'">Total : &nbsp;&nbsp;&nbsp;<b>'
                                             .$qty[0] .'</b>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .$qty[2] .'</label></div>';
                                                     
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
                                                echo '"><label class="label_values">&nbsp;' .$display_qty[$ct][1] .'</label></div><br/>';
                                                
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
                            <td><label class="mobile-header header-highlight">Transfer</label><label class="label_fields" for="loc_select<?php echo $prod_uom_recs[$cnt]->id;?>">Location (From):</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="loc-select<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="loc-select<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true">
                            <option data-content="None Selected">0</option>
                            <?php 
                            $loc_arr = array( explode( ',', $loc_list ), explode( '~|`', $loc_cd_list), explode( '~|`', $loc_name_list ) );                       

                            for ( $ct = 0; $ct < count( $loc_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($loc_arr[1][$ct]) .'   ' 
                                      .( $loc_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($loc_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['loc-select' .$prod_uom_recs[$cnt]->id]) 
                                            && $_POST['loc-select' .$prod_uom_recs[$cnt]->id] == $loc_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$loc_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                            <td><label class="label_fields" for="loc-select-to<?php echo $prod_uom_recs[$cnt]->id;?>">Location (To):</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="loc-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="loc-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true">
                            <option data-content="None Selected">0</option>
                            <?php 
                            $loc_arr = array( explode( ',', $loc_list ), explode( '~|`', $loc_cd_list), explode( '~|`', $loc_name_list ) );                       

                            for ( $ct = 0; $ct < count( $loc_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($loc_arr[1][$ct]) .'   ' 
                                      .( $loc_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($loc_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['loc-select-to' .$prod_uom_recs[$cnt]->id]) 
                                         && $_POST['loc-select-to' .$prod_uom_recs[$cnt]->id] == $loc_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$loc_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>">Stocks meant for (From):</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="client-meant-select<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true" 
                                >
                            <option data-content="None Selected">0</option>
                            <?php 
                            $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                            
                            for ( $ct = 0; $ct < count( $clients_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($clients_arr[1][$ct]) .'   ' 
                                      .( $clients_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['client-meant-select' .$prod_uom_recs[$cnt]->id]) 
                                        && $_POST['client-meant-select' .$prod_uom_recs[$cnt]->id] == $clients_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$clients_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                            <td><label class="label_fields" for="client-meant-select-to<?php echo $prod_uom_recs[$cnt]->id;?>">Stocks meant for (To):</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-meant-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                name="client-meant-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true" 
                                >
                            <option data-content="None Selected">0</option>
                            <?php 
                            $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                            
                            for ( $ct = 0; $ct < count( $clients_arr[0] ); $ct++ ) {
                                echo '<option data-content="'.htmlspecialchars($clients_arr[1][$ct]) .'   ' 
                                      .( $clients_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$ct]) .')' ) .'"'
                                      .( isset($_POST['client-meant-select-to' .$prod_uom_recs[$cnt]->id]) 
                                            && $_POST['client-meant-select-to' .$prod_uom_recs[$cnt]->id] == $clients_arr[0][$ct] ? ' selected=selected ' : '' )
                                        .'>'
                                        .$clients_arr[0][$ct] .'</option>';
                            }
                            ?>
                            </select></td>
                            
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="job_no<?php echo $prod_uom_recs[$cnt]->id;?>">Job No (From):</label></td>
                            <td><input type="text" class="form-control input_values" id="job_no<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                 name="job_no<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                                 value="<?php echo ( isset($_POST['job_no' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['job_no' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?>"></td>
                            <td><label class="label_fields" for="job_no_to<?php echo $prod_uom_recs[$cnt]->id;?>">Job No (To):</label></td>
                            <td><input type="text" class="form-control input_values" id="job_no_to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                 name="job_no_to<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                                 value="<?php echo ( isset($_POST['job_no_to' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['job_no_to' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?>"></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="remark<?php echo $prod_uom_recs[$cnt]->id;?>">Remarks (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="remark<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="remark<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="4000"
                            ><?php echo ( isset($_POST['remark' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['remark' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="remark_to<?php echo $prod_uom_recs[$cnt]->id;?>">Remarks (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="remark_to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="remark_to<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="4000"
                            ><?php echo ( isset($_POST['remark_to' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['remark_to' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add1-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 1 (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add1-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add1-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add1-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add1-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="add1-to-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 1 (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add1-to-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add1-to-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add1-to-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add1-to-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add2-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 2 (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add2-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add2-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add2-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add2-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="add2-to-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 2 (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add2-to-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add2-to-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add2-to-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add2-to-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                        </tr> 
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add3-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 3 (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add3-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add3-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add3-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add3-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="add3-to-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 3 (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add3-to-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add3-to-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add3-to-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add3-to-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                        </tr> 
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add4-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 4 (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add4-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add4-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add4-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add4-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="add4-to-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 4 (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add4-to-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add4-to-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add4-to-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add4-to-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>                            
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td><label class="label_fields" for="add5-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 5 (From):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add5-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add5-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add5-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add5-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                            <td><label class="label_fields" for="add5-to-<?php echo $prod_uom_recs[$cnt]->id;?>">Additional Field 5 (To):</label></td>
                            <td><pre><textarea class="form-control input_values" rows="5" id="add5-to-<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                      name="add5-to-<?php echo $prod_uom_recs[$cnt]->id;?>" maxlength="300"
                            ><?php echo ( isset($_POST['add5-to-' .$prod_uom_recs[$cnt]->id]) ? 
                                                     htmlspecialchars(addslashes($_POST['add5-to-' .$prod_uom_recs[$cnt]->id])) : '');
                                                     ?></textarea></pre></td>
                        </tr>
                        <tr>
                            <!-- 3rd column -->
                            <td rowspan=2><label class="label_fields">UOM Hierarchy :</label></td>
                            <td rowspan=2><?php   for ( $c = $prod_uom->get_first_uom_lvl(); $c <= $prod_uom->get_last_uom_lvl(); $c++ ) {
                                                // UOM label
                                                echo '<div class="box ';
                                                
                                                if ( $uoms[$c + $prod_uom->get_last_uom_lvl()][0] === null ) {
                                                    echo ' no-display';
                                                }
                                                
                                                if ( $c == $prod_uom_recs[$cnt]->uom_default_level ) {
                                                    echo ' default';
                                                }
                                                
                                                echo '"><input type="text" class="form-control input_values" id="qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" 
                                                        name="qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" data-rule-number=true data-rule-min="0"
                                                        value="' .( isset($_POST['qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c] ) ? 
                                                                    $_POST['qty' .$prod_uom_recs[$cnt]->id .'_' .(int) $c ] : '') 
                                                        .'"> <label class="label_values">&nbsp;' .$uoms[$c + $prod_uom->get_last_uom_lvl()][4] .'</label></div><br/>';
                                                        
                                                // hidden uom_id
                                                echo '<input type="text" class="no-display" id="uom_id_' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'"
                                                       name="uom_id_' .$prod_uom_recs[$cnt]->id .'_' .(int) $c .'" 
                                                       value="' .$uoms[$c + $prod_uom->get_last_uom_lvl()][0] .'"></input>';
                                        } ?>
                            </td>
                            <td><label class="label_fields" for="client-select-to<?php echo $prod_uom_recs[$cnt]->id;?>">Client Transfer To :</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                    name="client-select-to<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true" 
                                    onchange="getElementById('save_not_pressed').value = 'false'; this.form.submit();">
                                    <?php 
                                    $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                                    
                                    for ( $ct = 0; $ct < count( $clients_arr[0] ); $ct++ ) {
                                        echo '<option data-content="'.htmlspecialchars($clients_arr[1][$ct]) .'   ' 
                                              .( $clients_arr[2][$ct] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$ct]) .')' ) .'"';
                                              
                                              if ( isset($_POST['client-select-to'.$prod_uom_recs[$cnt]->id]) ) {
                                                  if ( $_POST['client-select-to'.$prod_uom_recs[$cnt]->id] == $clients_arr[0][$ct] ) {
                                                      echo ' selected=selected';
                                                  }
                                              } else { // default set to same client
                                                  if ( $clients_arr[0][$ct] == $_POST['client-select'] ) {
                                                      echo ' selected=selected';
                                                  }
                                              }
                                        
                                        echo '>' .$clients_arr[0][$ct] .'</option>';
                                    }
                                    ?>
                                    </select>
                            </td>
                        </tr>   
                        <tr>
                            <td><label class="label_fields" 
                                 for="id_to<?php echo $prod_uom_recs[$cnt]->id;?>">Product UOMs Set :</label></td>
                            <td><select class="selectpicker click-submit-form" data-style="btn-primary" id="id_to<?php echo $prod_uom_recs[$cnt]->id;?>" 
                                    name="id_to<?php echo $prod_uom_recs[$cnt]->id;?>" data-live-search="true" 
                                    data-rule-min="1" data-msg-min="Please choose an UOM set">
                                <option data-content="None Selected">0</option>
                                <?php
                                    $uom_rec = new \my_inventory\Myi_UOM_Record();
                                    
                                    $client_id = ( isset($_POST['client-select-to'.$prod_uom_recs[$cnt]->id] ) ? 
                                                                                          $_POST['client-select-to'.$prod_uom_recs[$cnt]->id] :
                                                                                          $_POST['client-select'] );
                                    
                                    $uom_recs = $uom_rec->get_record_by_cli_prd (   $client_id,
                                                                                    $_POST['prod-select'],
                                                                                    $user_role_for_client->get_role( get_current_user_id(), $client_id ),
                                                                                    'stock_mod_inventory' );
                                    
                                    for ($ct=0; $ct < count( $uom_recs); $ct++) {
                                        $uoms_for_rec = $uom_rec->get_uoms_in_arr_with_name( $uom_recs[$ct] );
                                        $same_smallest_uom = false;
                                        
                                        $element = '<option data-content="' .htmlspecialchars($uom_recs[$ct]->{"desc"}) .'(';
                                        
                                        for ($c=0; $c < count($uoms_for_rec); $c++ ) {
                                            if ($uoms_for_rec[$c][0] !== null && $uoms_for_rec[$c][0] != 0) {
                                                $element .= htmlspecialchars($uoms_for_rec[$c][4]);
                                                
                                                if ( $uoms_for_rec[$c][2] > 0 ) { //not last uom
                                                    $element .= ' <- (X ' .$uoms_for_rec[$c][2] .')';
                                                } else { // last uom
                                                    // if have same smallest uom
                                                    if ( $uoms_for_rec[$c][0] == $smallest_uom_id[$cnt] ) {
                                                        $same_smallest_uom = true;
                                                    }
                                                }
                                                
                                                if ($c+1 < count($uoms_for_rec) && $uoms_for_rec[$c+1][0] !== null && $uoms_for_rec[$c+1][0] != 0 ) {
                                                    $element .= ' <- ';
                                                }
                                            }
                                        }
                                        
                                        $element .= ')"';
                                        
                                        // if id is same as current id, but default will select it
                                        if ( $prod_uom_recs[$cnt]->id == $uom_recs[$ct]->id ) {
                                            $element .= ' selected=selected';
                                        }
                                        
                                        $element .= '>' .$uom_recs[$ct]->id .'</option>';
                                        
                                        if ( $same_smallest_uom ) {
                                            echo $element;
                                        }
                                    } // for
                                ?>
                            </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=4><button type="submit" id="save_btn_in_<?php echo $prod_uom_recs[$cnt]->id; ?>" 
                                           class="btn btn-primary"><span class="glyphicon glyphicon-transfer">    Transfer</span>
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
