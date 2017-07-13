<?php
namespace my_inventory\myi_inv_rpt;

require_once( plugin_dir_path( __FILE__ ) . '/../myi_inventory.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_product.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_location.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_user.php');
require_once( plugin_dir_path( __FILE__ ) . '/../myi_report.php');
    
if (!is_user_logged_in()) {
    echo 'You had been logged out. Please login again';
} else {

    if (isset($_GET['client'])) {
        $client_cd_allowed = stripslashes($_GET['client']);
    } else {
        $client_cd_allowed = null; // all clients
    }
    
    // already retrieved the allowed client_list
    if ( isset($_POST['client_list']) && isset($_POST['access_request']) && $_POST['access_request'] == 'view_reports' ) {
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
        $clients = $user_roles->get_clients( get_current_user_id(), 'view_reports', $client_cd_allowed );
        
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

        $object = new \my_inventory\Myi_Location();
        $locs = $object->get_location_list( $user_roles->get_saved_role() );
        
        if ($locs === false) {
            echo 'Error obtaining location list<br/><br/>';
        }
        
        $loc_list = $locs[0];
        $loc_cd_list = $locs[1];
        $loc_name_list = $locs[2];  
    }
    
    if (isset($_POST['client-select'])  
           && wp_verify_nonce( $_POST['inv_rpt_nonce'], 'inv_rpt' ) ) {
        
        $log = new \my_inventory\Myi_Report();
        $user_roles = new \my_inventory\Myi_User_Roles();
        
        $dt = explode('-',$_POST['daterange']);

        $my_results = $log->get_stock_count( ( count($dt) == 0 ? null : \my_inventory\myi_convert_date($dt[0]) ),
                                           //( count($dt) == 0 ? null : \my_inventory\myi_convert_date($dt[1],false) ),
                                            $_POST['client-select'],
                                            $_POST['prod-select'],
                                            $_POST['loc-select'],
                                            $_POST['client-select-for'],
                                            $_POST['cb'],
                                            $_POST['user-select'],
                                            get_current_user_id() );
    }
?>
<form id="my_form" name="my_form" method="post" action="#">
<div class="form-group"><pre class="no-display">
    <input type="hidden" name="access_request" id="access_request" value="view_reports" />
    <input type="hidden" name="client_list" id="client_list" value="<?php echo htmlspecialchars($client_list); ?>" />
    <input type="hidden" name="client_cd_list" id="client_cd_list" value="<?php echo htmlspecialchars($client_cd_list); ?>" />
    <input type="hidden" name="client_name_list" id="client_name_list" value="<?php echo htmlspecialchars($client_name_list); ?>" />
    <input type="hidden" name="prod_list" id="prod_list" value="<?php echo htmlspecialchars($prod_list); ?>" />
    <input type="hidden" name="prod_cd_list" id="prod_cd_list" value="<?php echo htmlspecialchars($prod_cd_list); ?>" />
    <input type="hidden" name="prod_name_list" id="prod_name_list" value="<?php echo htmlspecialchars($prod_name_list); ?>" />
    <input type="hidden" name="loc_list" id="loc_list" value="<?php echo htmlspecialchars($loc_list); ?>" />
    <input type="hidden" name="loc_cd_list" id="loc_cd_list" value="<?php echo htmlspecialchars($loc_cd_list); ?>" />
    <input type="hidden" name="loc_name_list" id="loc_name_list" value="<?php echo htmlspecialchars($loc_name_list); ?>" />
    </pre>
    <?php wp_nonce_field( 'inv_rpt', 'inv_rpt_nonce' ); ?>
        <div id="no-more-tables">
            <table class="col-md-12 table-condensed cf">
        		<thead class="cf">
        		</thead>
        		<tbody>
                <tr>
                        <td>As at Date (Clear the date to retrieve current stock count)</td>
                        <td><input type="text" class="input_values daterange" name="daterange" id="daterange" singleDatePicker=true
                             value="<?php echo ( isset($_POST['daterange']) ? $_POST['daterange'] : '');  ?>" />
                        </td>
                </tr>
       			<tr>
        				<td>Client</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select" name="client-select[]" data-live-search="true" 
                             multiple>
                            <option data-content="All Selected" <?php echo (!isset($_POST['client-select']) || $_POST['client-select'][0] == 0 ? 'selected' : ''); ?>
                            >0</option>
                        <?php 
                        $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $clients_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($clients_arr[1][$cnt]) .'   ' 
                                  .( $clients_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$cnt]) .')' ) .'"'
                                  .( isset($_POST['client-select']) && in_array( $clients_arr[0][$cnt], $_POST['client-select'] ) ? 'selected' : '') .'>'
                                    .$clients_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>
                </tr>
                <tr>
        				<td>Product</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="prod-select" name="prod-select[]" data-live-search="true" 
                             multiple>
                            <option data-content="All Selected" <?php echo (!isset($_POST['prod-select']) || $_POST['prod-select'][0] == 0 ? 'selected' : ''); ?>>0</option>
                        <?php 
                        $prod_arr = array( explode( ',', $prod_list ), explode( '~|`', $prod_cd_list), explode( '~|`', $prod_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $prod_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($prod_arr[1][$cnt]) .'   ' 
                                  .( $prod_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($prod_arr[2][$cnt]) .')' ) .'"'
                                    .( isset($_POST['prod-select']) && in_array( $clients_arr[0][$cnt], $_POST['prod-select'] ) ? 'selected' : '') .'>'
                                    .$prod_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>                        
        			</tr>
        			<tr>
        				<td>Location</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="loc-select" name="loc-select[]" data-live-search="true" 
                             multiple>
                            <option data-content="All Selected" <?php echo (!isset($_POST['loc-select']) || $_POST['loc-select'][0] == 0 ? 'selected' : ''); ?>>0</option>
                        <?php 
                        $loc_arr = array( explode( ',', $loc_list ), explode( '~|`', $loc_cd_list), explode( '~|`', $loc_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $loc_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($loc_arr[1][$cnt]) .'   ' 
                                  .( $loc_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($loc_arr[2][$cnt]) .')' ) .'"'
                                    .( isset($_POST['loc-select']) && in_array( $clients_arr[0][$cnt], $_POST['loc-select'] ) ? 'selected' : '') .'>'
                                    .$loc_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>
        			</tr>
       			<tr>
        				<td>Stock is for Client</td>
        				<td><select class="selectpicker click-submit-form" data-style="btn-primary" id="client-select-for" name="client-select-for[]" data-live-search="true" 
                             multiple>
                            <option data-content="All Selected" <?php echo (!isset($_POST['client-select-for']) || $_POST['client-select-for'][0] == 0 ? 'selected' : ''); ?>>0</option>
                        <?php 
                        $clients_arr = array( explode( ',', $client_list ), explode( '~|`', $client_cd_list), explode( '~|`', $client_name_list ) );                       
                        
                        for ( $cnt = 0; $cnt < count( $clients_arr[0] ); $cnt++ ) {
                            echo '<option data-content="'.htmlspecialchars($clients_arr[1][$cnt]) .'   ' 
                                  .( $clients_arr[2][$cnt] == '' ? '' : '(' .htmlspecialchars($clients_arr[2][$cnt]) .')' ) .'"'
                                    .( isset($_POST['client-select-for']) && in_array( $clients_arr[0][$cnt], $_POST['client-select-for'] ) ? 'selected' : '') .'>'
                                    .$clients_arr[0][$cnt] .'</option>';
                        }
                        ?>
                            </select>
                        </td>
                </tr>
                <tr>
                        <td>Display Fields (Tick to display)</td>
                        <td><!--<div class="checkbox"><label><input type="checkbox" value="1" id="cb[]" name="cb[]" 
                             <?php echo ( isset($_POST['cb']) && in_array('1',$_POST['cb']) ? 'checked' : '' ) ?>
                             >Client</label>
                                </div>-->
                            <div class="checkbox"><label><input type="checkbox" value="2" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('2',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Location</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="3" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('3',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Stock is for</label>
                                </div><br/>
                            <div class="checkbox"><label><input type="checkbox" value="5" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('5',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Job No.</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="6" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('6',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Remarks</label>
                                </div><br/>
                            <div class="checkbox"><label><input type="checkbox" value="7" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('7',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Add. Field 1</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="8" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('8',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Add. Field 2</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="9" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('9',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Add. Field 3</label>
                                </div><br/>
                            <div class="checkbox"><label><input type="checkbox" value="10" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('10',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Add. Field 4</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="11" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('11',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Add. Field 5</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="13" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('13',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Prod UOM Desc</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="4" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('4',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Smallest UOM Only</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="12" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('12',$_POST['cb']) ? 'checked' : '' ) ?>
                            >UOM show full name</label>
                                </div>
                            <div class="checkbox"><label><input type="checkbox" value="14" id="cb[]" name="cb[]"
                            <?php echo ( isset($_POST['cb']) && in_array('14',$_POST['cb']) ? 'checked' : '' ) ?>
                            >Show zero stocks</label>
                                </div>
                        </td>
                </tr>
                <tr><td></td><td></td>
                </tr>
                <tr>
                            <td></td><td><button type="submit" id="save_btn" class="btn btn-primary"><span class="glyphicon glyphicon-file">    Generate</span>
                                        </button></td>
                </tr>               
        		</tbody>
        	</table>
        </div>
</div>      
</form>
<?php if (isset($_POST['client-select'])) {
?>    
    <table id="report" name="report">
    <thead>
        <tr>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Client Code</th>
            <th>Client Name</th>
            <?php 
            if ( isset( $_POST['cb'] )) {
                foreach( $_POST['cb'] as $val ) {
                    switch ( $val ) {
                        case '2' :  echo '<th>Location Code</th><th>Location Name</th>';
                                    break;
                        case '3' :  echo '<th>Stock meant for Client Code</th><th>Stock Meant for Client Name</th>';
                                    break;
                        case '5' :  echo '<th>Job No</th>';
                                    break;
                        case '6' :  echo '<th>Remark</th>';
                                    break;
                        case '7' :  echo '<th>A. Field 1</th>';
                                    break;
                        case '8' :  echo '<th>A. Field 2</th>';
                                    break;
                        case '9' :  echo '<th>A. Field 3</th>';
                                    break;
                        case '10' : echo '<th>A. Field 4</th>';
                                    break;
                        case '11' : echo '<th>A. Field 5</th>';
                                    break;
                        case '13' : echo '<th>Prod UOM Set</th>';
                                    break;
                    }
                } // foreach
            }
            ?>
            <th>Qty</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ( $my_results ) {
        foreach ( $my_results as $row ) {
            echo '<tr>';
            echo '<td>' .htmlspecialchars($row->prod_cd) .'</td>';
            echo '<td>' .htmlspecialchars($row->prod_name) .'</td>';
            echo '<td>' .htmlspecialchars($row->client_cd) .'</td>';
            echo '<td>' .htmlspecialchars($row->client_name) .'</td>';
            
            if ( isset( $_POST['cb'] ) ) {
                foreach( $_POST['cb'] as $val ) {
                    switch ( $val ) {
                        case '2' :  echo '<td>' .htmlspecialchars($row->location_cd) .'</td>';
                                    echo '<td>' .htmlspecialchars($row->location_name) .'</td>';
                                    break;
                        case '3' :  echo '<td>' .htmlspecialchars($row->for_client_cd) .'</td>';
                                    echo '<td>' .htmlspecialchars($row->client_name_for) .'</td>';
                                    break;
                        case '5' :  echo '<td>' .htmlspecialchars($row->job_no) .'</td>';
                                    break;
                        case '6' :  echo '<td><pre>' .htmlspecialchars($row->remarks) .'</pre></td>';
                                    break;
                        case '7' :  echo '<td><pre>' .htmlspecialchars($row->{"add-field1"}) .'</pre></td>';
                                    break;
                        case '8' :  echo '<td><pre>' .htmlspecialchars($row->{"add-field2"}) .'</pre></td>';
                                    break;
                        case '9' :  echo '<td><pre>' .htmlspecialchars($row->{"add-field3"}) .'</pre></td>';
                                    break;
                        case '10' : echo '<td><pre>' .htmlspecialchars($row->{"add-field4"}) .'</pre></td>';
                                    break;
                        case '11' : echo '<td><pre>' .htmlspecialchars($row->{"add-field5"}) .'</pre></td>';
                                    break;
                        case '13' : echo '<td>' .htmlspecialchars($row->uom_set_desc) .'</td>';
                                    break;
                    }
                } // foreach
            }
            
            echo '<td>' .htmlspecialchars($row->uom) .'</td>';
            echo '</tr>';
        } // foreach
    } //if
    ?>
    </tbody>
    </table>
<?php
} // if isset($_POST['client-select']) ?>

<?php
} // user logged in
//wp_footer();
?>
