<?php
namespace my_inventory\myi_add_prod_uom;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_uom_record.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_product.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_uom.php');

function display_uom_record( $cnt, $id, $first_uom_lvl, $last_uom_lvl, $desc, $uom_default_level, $uoms, $uom_list, $uom_cd_list, $uom_name_list ) { 
        $tmp = new \my_inventory\Myi_UOM_Record();

        if ( $cnt === -1 ) { // new records
            $uoms = array();
            $id = 0;

            for ($c = 0; $c <= $tmp->get_last_uom_lvl() - $tmp->get_first_uom_lvl(); $c++ ) {
                //uom_id, uom_qty_in_smallest_uom, next_uom_qty, existing_uom, uom_shortname, uom_shortname_p
                array_push($uoms, array( null, 1, 0, false, '', '' ));
            }
        }
?>
                <p class="spacing-vert">&nbsp;</p>
                <!-- hidden -->
                <div class="checkbox no-display">
                     <!-- value is id_recordsRow -->
                     <label 
                     <?php if ( isset($_POST['rec_modified']) ) {
                                        foreach ( $_POST['rec_modified'] as $rec ) {
                                            if ( explode('_', $rec)[1] == $cnt ) {
                                                echo 'class="active"';
                                                break;
                                            }
                                        } //foreach
                           }?>
                     ><input type="checkbox" name="rec_modified[]" id="rec_modified<?php echo ((int) $cnt); ?>" 
                             value="<?php echo $id .'_' .(int) $cnt; ?>">
                     </label>
                </div>
                
                <?php if ($cnt === -1) { ?>
                     <input type="text" class="no-display" name="min_level"
                            id="min_level" value="<?php echo $tmp->get_first_uom_lvl(); ?>"></input>
                     <input type="text" class="no-display" name="max_level"
                            id="max_level" value="<?php echo $tmp->get_last_uom_lvl(); ?>"></input>
                <?php } ?>
                
                <?php if ($cnt === -1) { // new record added
                            echo '<div class="new_record"><span class="new_record">Add New Record</span></div>';
                      } ?>
                                            
                <!-- end hidden -->
                <div id="no-more-tables<?php echo $cnt; ?>">
                    <table class="col-md-12 table-bordered table-striped table-condensed cf">
                        <thead class="cf">
                        </thead>
                        <tbody>
                        <tr>
                            <td><label class="label_fields">Description :</label></td>
                            <td><pre><input type="text" class="form-control input_values" id="desc<?php echo ((int) $cnt); ?>" name="desc<?php echo ((int) $cnt); ?>" maxlength="300"
                                      value="<?php echo htmlspecialchars($desc); ?>"
                                      onchange="getElementById('rec_modified<?php echo ((int) $cnt); ?>').checked = true;"></input></pre></td>
                        </tr>
                        <tr>
                            <td><label class="label_fields">UOMs hierarchy :</label></td>
                            <td><div class="uom-levels">
                                <?php
                                        $first_set_lvl = -999;
                                        $last_set_lvl = $last_uom_lvl;

                                        for ( $c = $first_uom_lvl; $c <= $last_uom_lvl; $c++ ) {
                                            if ( $first_set_lvl === -999 && !($uoms[$c + $tmp->get_last_uom_lvl()][0] === null) ) {
                                                $first_set_lvl = $c;
                                            } else if ( $first_set_lvl != -999 && $last_set_lvl == $tmp->get_last_uom_lvl() 
                                                        && ($uoms[$c + $tmp->get_last_uom_lvl()][0] === null) ) {
                                                $last_set_lvl = $c - 1;
                                            }
                                        } // for?>

                                        <!-- hidden fields -->
                                        <input type="text" class="no-display" name="first_set_lvl<?php echo ((int) $cnt); ?>"
                                            id="first_set_lvl<?php echo ((int) $cnt); ?>" value="<?php echo $first_set_lvl; ?>"></input>
                                        <input type="text" class="no-display" name="last_set_lvl<?php echo ((int) $cnt); ?>"
                                            id="last_set_lvl<?php echo ((int) $cnt); ?>" value="<?php echo $last_set_lvl; ?>"></input>                                          
                                        <!-- end hidden -->
                                        <?php
                                        
                                        if ( $first_set_lvl > $tmp->get_first_uom_lvl() ) {
                                            echo '<button type="submit" id="add_btn_up' .((int) $cnt) .'" class="btn btn-primary"
                                                   onclick="add_uom_clicked(' .((int) $cnt) .', true );return false;"><span class="glyphicon glyphicon-triangle-top">    Add UOM</span></button><br/>';
                                        } else {
                                            echo '<button type="submit" id="add_btn_up' .((int) $cnt) .'" class="btn btn-primary no-display"
                                                   onclick="add_uom_clicked(' .((int) $cnt) .', true );return false;"><span class="glyphicon glyphicon-triangle-top">    Add UOM</span></button><br/>';
                                        }
                                        
                                        echo '<br/>';
                                        
                                        for ( $c = $tmp->get_first_uom_lvl(); $c <= $tmp->get_last_uom_lvl(); $c++ ) {
                                                // UOM label
                                                echo '<div id="box' .((int) $cnt) .'_' .((int) $c) .'" name="box' .((int) $cnt) .'_' .((int) $c) .'" class="box ';
                                                
                                                if ( $c == $uom_default_level ) {
                                                    echo ' default';
                                                }
                                                
                                                if ( $uoms[$c + $tmp->get_last_uom_lvl()][0] === null ) {
                                                    echo ' no-display';
                                                } ?>
                                                "><input type="radio" class="right" name="rb<?php echo (int) $cnt; ?>" 
                                                    id="rb<?php echo (int) $cnt; ?>" value="<?php echo (int) $c; ?>"
                                                    onclick="set_default_uom(<?php echo (int) $cnt; ?>,<?php echo (int) $c; ?>);getElementById('rec_modified<?php echo ((int) $cnt); ?>').checked = true;"
                                                    <?php
                                                        if ($c == $uom_default_level) {
                                                            echo ' checked=checked';
                                                        }
                                                    ?>
                                                    >
                                                <?php
                                                if ( $uoms[$c + $tmp->get_last_uom_lvl()][0] === null || $uoms[$c + $tmp->get_last_uom_lvl()][0] == 0 ) {
                                                    ?>
                                                    <select class="selectpicker click-submit-form" data-style="btn-primary" id="uom-select<?php echo ((int) $cnt) .'_' .((int) $c); ?>" 
                                                            name="uom-select<?php echo ((int) $cnt) .'_' .((int) $c); ?>" data-live-search="true" 
                                                            onchange="getElementById('rec_modified<?php echo ((int) $cnt); ?>').checked = true;(jQuery)(this).valid();return false;" 
                                                            data-rule-min="1" data-msg-min="Please choose an UOM">
                                                            <option data-content="None Selected">0</option>
                                                            <?php 
                                                                $uom_arr = array( explode( ',', $uom_list ), explode( '~|`', $uom_cd_list), explode( '~|`', $uom_name_list ) );                                                               
                        
                                                                for ( $c2 = 0; $c2 < count( $uom_arr[0] ); $c2++ ) {
                                                                    echo '<option data-content="'.htmlspecialchars($uom_arr[1][$c2]) .'   ' 
                                                                          .( $uom_arr[2][$c2] == '' ? '' : '(' .htmlspecialchars($uom_arr[2][$c2]) .')' ) .'"'
                                                                          .( isset($_POST['uom-select' .((int) $cnt) .'_' .((int) $c)]) 
                                                                                && $cnt != -1
                                                                                && $_POST['uom-select' .((int) $cnt) .'_' .((int) $c)] == $uom_arr[0][$c2] ? ' selected=selected ' : '' )
                                                                            .'>'
                                                                            .$uom_arr[0][$c2] .'</option>';
                                                                }
                                                            ?>
                                                    </select>
                                                    <br/>
                                                    <button name="btn_del<?php echo ((int) $cnt) .'_' .((int) $c); ?>" 
                                                            id="btn_del<?php echo ((int) $cnt) .'_' .((int) $c); ?>" type="button" class="btn btn-danger no-display"
                                                            onclick="delete_uom_clicked(<?php echo ((int) $cnt ); ?>,
                                                          <?php
                                                                if ( $c < $first_set_lvl ) {
                                                                    echo 'true';
                                                                } else if ($c >= $last_set_lvl ) {
                                                                    echo 'false';
                                                                }
                                                          ?>
                                                          )"><span class="glyphicon glyphicon-remove">    Delete this UOM</span></button>
                                                    </div><br/>
                                                    <?php
                                                    // qty
                                                    echo '<span class="glyphicon glyphicon-arrow-up no-display"
                                                            name="qty' .((int) $cnt) .'_' .((int) $c) .'" id="qty' .((int) $cnt) .'_' .((int) $c) .'"
                                                          > (X <input type="text" 
                                                           class="form-control input_values qty_input" required data-rule-number=true data-rule-min="1"
                                                           name="qtyInp' .((int) $cnt) .'_' .((int) $c) .'" id="qtyInp' .((int) $cnt) .'_' .((int) $c) .'"
                                                           onchange="getElementById(\'rec_modified' .((int) $cnt) .'\').checked = true;" value="0"></input>)</span>';                                                    
                                                } else { // existing uom. disallow changes except for last level uom qty
                                                    echo '<label class="label_values ';

                                                    // no-display class
                                                    if ( $uoms[$c + $tmp->get_last_uom_lvl()][0] === null ) {
                                                            echo ' no-display';
                                                    }

                                                    echo '">' .$uoms[$c + $tmp->get_last_uom_lvl()][4] .'</label></div><br/>';
                                                    
                                                    // hidden id
                                                    echo '<input type="text" class="no-display" id="uom-select' .((int) $cnt) .'_' .((int) $c) .'"
                                                            name="uom-select' .((int) $cnt) .'_' .((int) $c) .'" value="' .$uoms[$c + $tmp->get_last_uom_lvl()][0]
                                                         .'"></input>';

                                                    // qty
                                                    echo '<span name="qty' .((int) $cnt) .'_' .((int) $c) .'" id="qty' .((int) $cnt) .'_' .((int) $c) .'" 
                                                           class="glyphicon glyphicon-arrow-up ';
                                                    if ( $uoms[$c + $tmp->get_last_uom_lvl()][0] === null || $uoms[$c + $tmp->get_last_uom_lvl()][2] == 0 ) {
                                                            echo 'no-display';
                                                    }
                                                    
                                                    if ( $c < $first_set_lvl || $c >= $last_set_lvl ) {
                                                        echo '"> (X <input type="text" 
                                                          class="form-control input_values qty_input" required data-rule-number=true data-rule-min="1"
                                                          name="qtyInp' .((int) $cnt) .'_' .((int) $c) .'" id="qtyInp' .((int) $cnt) .'_' .((int) $c) .'"
                                                          onchange="getElementById(\'rec_modified' .((int) $cnt) .'\').checked = true;" value="0"></input>)</span>';
                                                    } else { // display label instead of textbox
                                                        echo '"> (X ' .$uoms[$c + $tmp->get_last_uom_lvl()][2] .')</span>';
                                                        // hidden qty
                                                        echo '<input type="text" class="no-display" id="qtyInp' .((int) $cnt) .'_' .((int) $c) 
                                                            .'" name="qtyInp' .((int) $cnt) .'_' .((int) $c) .'" value="' .$uoms[$c + $tmp->get_last_uom_lvl()][2]
                                                            .'" required data-rule-number=true data-rule-min="1"></input>';
                                                    }
                                                }

                                                // UOM next level qty

                                        } //for

                                        if ( $last_set_lvl < $tmp->get_last_uom_lvl() ) {
                                            echo '<button type="submit" id="add_btn_down' .((int) $cnt) .'" class="btn btn-primary"
                                                    onclick="add_uom_clicked(' .((int) $cnt) .', false );return false;"><span class="glyphicon glyphicon-triangle-bottom">    Add UOM</span>
                                                </button>';
                                        } else {
                                            echo '<button type="submit" id="add_btn_down' .((int) $cnt) .'" class="btn btn-primary no-display"
                                                    onclick="add_uom_clicked(' .((int) $cnt) .', false );return false;"><span class="glyphicon glyphicon-triangle-bottom">    Add UOM</span>
                                                </button>';
                                        }
                                        
                                ?>
                                </div>
                            </td>
                        </tr>  
                  <?php if ($cnt === -1 ) { // add new record
                  ?>
                        <tr>
                            <td colspan=100%></td>
                        </tr>                          
                        <tr>
                            <td></td>
                            <td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save">    Save</span>
                                                </button></td>
                        </tr>                  
                  <?php } //if ($cnt === -1 )
                  ?>
                        </tbody>
                    </table>            
                </div>    
<?php } // end function

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
        
        $prod_list = stripslashes($_POST['prod_list']);
        $prod_cd_list = stripslashes($_POST['prod_cd_list']);
        $prod_name_list = stripslashes($_POST['prod_name_list']);

        $uom_list = stripslashes($_POST['uom_list']);
        $uom_cd_list = stripslashes($_POST['uom_cd_list']);
        $uom_name_list = stripslashes($_POST['uom_name_list']);          
    } else { // get the allowed client_list
        $user_roles = new \my_inventory\Myi_User_Roles();
        $clients = $user_roles->get_clients( get_current_user_id(), 'view_inventory_master', $client_cd_allowed );
        
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

        $object = new \my_inventory\Myi_UOM();
        $uoms_list = $object->get_uoms_list( $user_roles->get_role_ignore_client( get_current_user_id() ) );
        
        if ($uom_list === false) {
            echo 'Error obtaining UOMs list<br/><br/>';
        }
        
        $uom_list = $uoms_list[0];
        $uom_cd_list = $uoms_list[1];
        $uom_name_list = $uoms_list[2];            
    }

    if (isset($_POST['client-select']) && trim($_POST['client-select'] != 0) && trim($_POST['prod-select'] != 0) 
          && wp_verify_nonce( $_POST['add_prod_uom_nonce'], 'add_prod_uom' ) ) {
        if ( isset( $_POST['rec_modified'] ) ) {
            $rec_succ = 0;
            $rec_fail = 0;
            
            foreach( $_POST['rec_modified'] as $rec ) {
                $expl_arr = explode('_', $rec);
                $id = $expl_arr[0];
                $row = $expl_arr[1];
                
                $desc = $_POST['desc'.$row];
                $default_uom = $_POST['rb'.$row];
                
                $uom_arr = array();
                
                $uom_rec = new \my_inventory\Myi_UOM_Record();
                $user_roles = new \my_inventory\Myi_User_Roles();
                
                for ($cnt = $uom_rec->get_first_uom_lvl(); $cnt <= $uom_rec->get_last_uom_lvl(); $cnt++){
                    array_push($uom_arr, array($_POST['uom-select'.$row .'_' .$cnt], $_POST['qtyInp'.$row .'_' .$cnt]));
                }
                
                // new record
                if ($row == -1 && $_POST['last_set_lvl'.$row] >= $uom_rec->get_first_uom_lvl() ) { // new record and have insert records
                    $uom_rec->set_client_and_prod_id( $_POST['client-select'], $_POST['prod-select'] );


                    if ( $uom_rec->initial_uom_add( $desc, 
                                                    get_current_user_id(),
                                                    $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ),
                                                    $default_uom, 
                                                    $uom_rec->strip_empty_uom( $uom_arr ) ) ) {
                        $rec_succ++;
                    } else {
                        $rec_fail++;
                    }
                } else if ($row != -1){ // existing records
                    $uom_rec->get_record_by_id( $id, $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ) );

                    if ( $uom_rec->update_uom(  $desc, 
                                                get_current_user_id(),
                                                $user_roles->get_role( get_current_user_id(), $_POST['client-select'] ),
                                                $default_uom, 
                                                $uom_arr ) ) {
                        $rec_succ++;
                    } else {
                        $rec_fail++;
                    }
                } // if ($row == -1)
            } // foreach

            if ( $rec_succ <= 0 && $rec_succ + $rec_fail > 0 ) {
                    echo '<span id="err_msg" class="glyphicon glyphicon-thumbs-down">    Add/Modify Product UOMs failed. ' .$rec_fail .' updates/insertion failed... Ensure that you have rights to add/modify the product UOM and that the product UOM does not exists</span><br/>';
            } else if ( $rec_succ + $rec_fail > 0 ){
                    echo '<span id="succ_msg" class="glyphicon glyphicon-thumbs-up">    ' .$rec_succ .' Product UOM added/modify successfully...' 
                         . ( $rec_fail > 0 ? $rec_fail .' Product UOM added/modify failed...' : '') .'</span><br/>';
            }
        } // if ( isset( $_POST['rec_modified'] ) ) 
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="create_inventory_master" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="prod_list" id="prod_list" value="<?php echo htmlspecialchars($prod_list); ?>" />
    <input type="hidden" name="prod_cd_list" id="prod_cd_list" value="<?php echo htmlspecialchars($prod_cd_list); ?>" />
    <input type="hidden" name="prod_name_list" id="cat_name_list" value="<?php echo htmlspecialchars($prod_name_list); ?>" />
    <input type="hidden" name="uom_list" id="uom_list" value="<?php echo htmlspecialchars($uom_list); ?>" />
    <input type="hidden" name="uom_cd_list" id="uom_cd_list" value="<?php echo htmlspecialchars($uom_cd_list); ?>" />
    <input type="hidden" name="uom_name_list" id="uom_name_list" value="<?php echo htmlspecialchars($uom_name_list); ?>" />
    <input type="hidden" name="btn_disabled" id="btn_disabled" value="" />
    </pre>
    <?php wp_nonce_field( 'add_prod_uom', 'add_prod_uom_nonce' ); ?>
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
                $user_role_for_client = new \my_inventory\Myi_User_Roles();

                $prod_uom = new \my_inventory\Myi_UOM_Record();
                $prod_uom_recs = $prod_uom->get_record_by_cli_prd ( $_POST['client-select'],
                                                                    $_POST['prod-select'],
                                                                    $user_role_for_client->get_role( get_current_user_id(), $_POST['client-select'] ) );

                /*if ( $prod_uom_recs === false ) {
                    echo 'Error obtaining products UOMs<br/><br/>';
                }*/                
                for ($cnt=0; $cnt < count( $prod_uom_recs); $cnt++) {
                    $uoms = $prod_uom->get_uoms_in_arr_with_name( $prod_uom_recs[$cnt] );

                    display_uom_record( $cnt, 
                                        $prod_uom_recs[$cnt]->id, 
                                        $prod_uom->get_first_uom_lvl(), 
                                        $prod_uom->get_last_uom_lvl(), 
                                        $prod_uom_recs[$cnt]->{"desc"}, 
                                        $prod_uom_recs[$cnt]->uom_default_level, 
                                        $uoms,
                                        $uom_list,
                                        $uom_cd_list,
                                        $uom_name_list );
                ?>
          <?php } // for           
          
          //display_uom_record( $cnt, $id, $first_uom_lvl, $last_uom_lvl, $desc, $uom_default_level, $uoms, $uom_list, $uom_cd_list, $uom_name_list )
          // create new record
          display_uom_record( -1, 0, -10, -10, '', -9, null, $uom_list, $uom_cd_list, $uom_name_list );
          } // if ( isset($_POST['client-select']) && $_POST['client-select'] != 0 )
          ?>   
</div>                
</form>

<?php
} //user logged in
//wp_footer();
?>
