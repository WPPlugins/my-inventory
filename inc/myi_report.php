<?php
namespace my_inventory;

if ( ! class_exists( '\\my_inventory\\Myi_Report' ) ) { 
    class Myi_Report {  
        public function __construct() {     
        }
        

        /**
        *  Get the inventory report
        *
        *  @param string $date if null, will get all. must be in YYYY-MM-DD HH24:MM:SS format. The stock count as at this date
        *  @param array of string $client the list of clients. If empty or 1st element id is 0, will get all
        *  @param array of string $prod the list of product. If empty or 1st element id is 0, will get all
        *  @param array of string $loc the list of location. If empty or 1st element id is 0, will get all
        *  @param array of string $client_for the list of clients_for. If empty or 1st element id is 0, will get all
        *  @param array of int $display_field. Field to display and not group by
        *  @param array of string $user the list of user. If empty or 1st element id is 0, will get all
        *  @param int @caller_user_id user_id of person running the log
        *  @return object/int if successful, false if fail
        */  
        public function get_stock_count( $date, $client, $prod, $loc,
                                            $client_for, $display_field, $user, $caller_user_id ) {

            global $wpdb;


            $select_col = ' SELECT * FROM ( SELECT  g.prod_cd, g.prod_name, f.client_cd, f.client_name, sum(a.smallest_uom_qty) as smallest_uom_qty';
            $group_by = ' GROUP BY g.prod_cd, g.prod_name, f.client_cd, f.client_name';
            
            for ( $c = count($display_field); $c >= 0; $c-- ) {
                switch ( $display_field[$c] ) {
                    case 1 :    // client
                                $select_col .= ', f.client_cd, f.client_name';
                                $group_by .= ', f.client_cd, f.client_name';
                                break;
                    case 2 :    // location
                                $select_col .= ', d.location_cd, d.location_name';
                                $group_by .= ', d.location_cd, d.location_name';
                                break;
                    case 3 :    // Stock meant for
                                $select_col .= ', e.client_cd as for_client_cd, e.client_name as client_name_for';
                                $group_by .= ', e.client_cd, e.client_name';
                                break;
                    // smallest uom only will be handled outside the loop
                    case 5 :    // Job No
                                $select_col .= ', trim(ucase(a.job_no)) as job_no';
                                $group_by .= ', trim(ucase(a.job_no))';
                                break;
                    case 6 :    // Remarks
                                $select_col .= ', trim(a.remarks) as remarks';
                                $group_by .= ', trim(a.remarks)';
                                break;
                    case 7 :    // Additional Field 1
                                $select_col .= ', trim(a.`add-field1`) as `add-field1`';
                                $group_by .= ', trim(a.`add-field1`)';
                                break;
                    case 8 :    // Additional Field 2
                                $select_col .= ', trim(a.`add-field2`) as `add-field2`';
                                $group_by .= ', trim(a.`add-field2`)';
                                break;
                    case 9 :    // Additional Field 3
                                $select_col .= ', trim(a.`add-field3`) as `add-field3`';
                                $group_by .= ', trim(a.`add-field3`)';
                                break;
                    case 10 :   // Additional Field 4
                                $select_col .= ', trim(a.`add-field4`) as `add-field4`';
                                $group_by .= ', trim(a.`add-field4`)';
                                break;
                    case 11 :   // Additional Field 5
                                $select_col .= ', trim(a.`add-field5`) as `add-field5`';
                                $group_by .= ', trim(a.`add-field5`)';
                                break;
                    // UOM show full name will be handled outside the loop
                    case 13 :   // Additional Field 5
                                $select_col .= ', trim(a.`desc`) as uom_set_desc';
                                $group_by .= ', trim(a.`desc`)';
                                break;
                }
            }
                       
            // handle smallest uom (4) and UOM show full name (12)
            if ( isset( $display_field ) && in_array( '4', $display_field ) ) { // display only smallest UOM                
                // UOM show full name
                if ( in_array( '12', $display_field ) ) {
                    $select_col .= ', concat( sum(a.smallest_uom_qty),\' \', 
                                        if( sum(a.smallest_uom_qty) > 1,
                                            if(min(a.smallest_uom_full_p)=\'\' OR min(a.smallest_uom_full_p) is null, min(a.smallest_uom_short_p), min(a.smallest_uom_full_p)),
                                            if(min(a.smallest_uom_full)=\'\' OR min(a.smallest_uom_full) is null, min(a.smallest_uom_short), min(a.smallest_uom_full))
                                            )) as uom';
                    $group_by .= ', a.smallest_uom_id';
                } else { // show short name
                    $select_col .= ', concat( sum(a.smallest_uom_qty),\' \',
                                        if ( sum(a.smallest_uom_qty) > 1, min(a.smallest_uom_short_p), min(a.smallest_uom_short))) as uom';
                    $group_by .= ', a.smallest_uom_id';
                }
            } else { // display all UOM
                // UOM show full name
                if ( isset( $display_field ) && in_array( '12', $display_field ) ) {
                    $select_col .= ', prod_uom_id, ' .$wpdb->prefix .'myi_udf_get_display_uom( sum(a.smallest_uom_qty),
                                   if( min(`l-9_uom_full`) is null OR trim(min(`l-9_uom_full`)) = \'\', min(`l-9_uom_short`), min(`l-9_uom_full`) ), 
                                        if( min(`l-9_uom_full_p`) is null OR trim(min(`l-9_uom_full_p`)) = \'\', min(`l-9_uom_short_p`), min(`l-9_uom_full_p`) ),
                                        min(`l-9_qty`), min(`l-9_qty_nxt_lvl`),
                                   if( min(`l-8_uom_full`) is null OR trim(min(`l-8_uom_full`)) = \'\', min(`l-8_uom_short`), min(`l-8_uom_full`) ), 
                                        if( min(`l-8_uom_full_p`) is null OR trim(min(`l-8_uom_full_p`)) = \'\', min(`l-8_uom_short_p`), min(`l-8_uom_full_p`) ),
                                        min(`l-8_qty`), min(`l-8_qty_nxt_lvl`),
                                   if( min(`l-7_uom_full`) is null OR trim(min(`l-7_uom_full`)) = \'\', min(`l-7_uom_short`), min(`l-7_uom_full`) ), 
                                        if( min(`l-7_uom_full_p`) is null OR trim(min(`l-7_uom_full_p`)) = \'\', min(`l-7_uom_short_p`), min(`l-7_uom_full_p`) ),
                                        min(`l-7_qty`), min(`l-7_qty_nxt_lvl`),
                                   if( min(`l-6_uom_full`) is null OR trim(min(`l-6_uom_full`)) = \'\', min(`l-6_uom_short`), min(`l-6_uom_full`) ), 
                                        if( min(`l-6_uom_full_p`) is null OR trim(min(`l-6_uom_full_p`)) = \'\', min(`l-6_uom_short_p`), min(`l-6_uom_full_p`) ),
                                        min(`l-6_qty`), min(`l-6_qty_nxt_lvl`),
                                   if( min(`l-5_uom_full`) is null OR trim(min(`l-5_uom_full`)) = \'\', min(`l-5_uom_short`), min(`l-5_uom_full`) ), 
                                        if( min(`l-5_uom_full_p`) is null OR trim(min(`l-5_uom_full_p`)) = \'\', min(`l-5_uom_short_p`), min(`l-5_uom_full_p`) ),
                                        min(`l-5_qty`), min(`l-5_qty_nxt_lvl`),
                                   if( min(`l-4_uom_full`) is null OR trim(min(`l-4_uom_full`)) = \'\', min(`l-4_uom_short`), min(`l-4_uom_full`) ), 
                                        if( min(`l-4_uom_full_p`) is null OR trim(min(`l-4_uom_full_p`)) = \'\', min(`l-4_uom_short_p`), min(`l-4_uom_full_p`) ),
                                        min(`l-4_qty`), min(`l-4_qty_nxt_lvl`),
                                   if( min(`l-3_uom_full`) is null OR trim(min(`l-3_uom_full`)) = \'\', min(`l-3_uom_short`), min(`l-3_uom_full`) ), 
                                        if( min(`l-3_uom_full_p`) is null OR trim(min(`l-3_uom_full_p`)) = \'\', min(`l-3_uom_short_p`), min(`l-3_uom_full_p`) ),
                                        min(`l-3_qty`), min(`l-3_qty_nxt_lvl`),
                                   if( min(`l-2_uom_full`) is null OR trim(min(`l-2_uom_full`)) = \'\', min(`l-2_uom_short`), min(`l-2_uom_full`) ), 
                                        if( min(`l-2_uom_full_p`) is null OR trim(min(`l-2_uom_full_p`)) = \'\', min(`l-2_uom_short_p`), min(`l-2_uom_full_p`) ),
                                        min(`l-2_qty`), min(`l-2_qty_nxt_lvl`),
                                   if( min(`l-1_uom_full`) is null OR trim(min(`l-1_uom_full`)) = \'\', min(`l-1_uom_short`), min(`l-1_uom_full`) ), 
                                        if( min(`l-1_uom_full_p`) is null OR trim(min(`l-1_uom_full_p`)) = \'\', min(`l-1_uom_short_p`), min(`l-1_uom_full_p`) ),
                                        min(`l-1_qty`), min(`l-1_qty_nxt_lvl`),
                                   if( min(`l0_uom_full`) is null OR trim(min(`l0_uom_full`)) = \'\', min(`l0_uom_short`), min(`l0_uom_full`) ), 
                                        if( min(`l0_uom_full_p`) is null OR trim(min(`l0_uom_full_p`)) = \'\', min(`l0_uom_short_p`), min(`l0_uom_full_p`) ),
                                        min(`l0_qty`), min(`l0_qty_nxt_lvl`),
                                   if( min(`l1_uom_full`) is null OR trim(min(`l1_uom_full`)) = \'\', min(`l1_uom_short`), min(`l1_uom_full`) ), 
                                        if( min(`l1_uom_full_p`) is null OR trim(min(`l1_uom_full_p`)) = \'\', min(`l1_uom_short_p`), min(`l1_uom_full_p`) ),
                                        min(`l1_qty`), min(`l1_qty_nxt_lvl`),
                                   if( min(`l2_uom_full`) is null OR trim(min(`l2_uom_full`)) = \'\', min(`l2_uom_short`), min(`l2_uom_full`) ), 
                                        if( min(`l2_uom_full_p`) is null OR trim(min(`l2_uom_full_p`)) = \'\', min(`l2_uom_short_p`), min(`l2_uom_full_p`) ),
                                        min(`l2_qty`), min(`l2_qty_nxt_lvl`),
                                   if( min(`l3_uom_full`) is null OR trim(min(`l3_uom_full`)) = \'\', min(`l3_uom_short`), min(`l3_uom_full`) ), 
                                        if( min(`l3_uom_full_p`) is null OR trim(min(`l3_uom_full_p`)) = \'\', min(`l3_uom_short_p`), min(`l3_uom_full_p`) ),
                                        min(`l3_qty`), min(`l3_qty_nxt_lvl`),
                                   if( min(`l4_uom_full`) is null OR trim(min(`l4_uom_full`)) = \'\', min(`l4_uom_short`), min(`l4_uom_full`) ), 
                                        if( min(`l4_uom_full_p`) is null OR trim(min(`l4_uom_full_p`)) = \'\', min(`l4_uom_short_p`), min(`l4_uom_full_p`) ),
                                        min(`l4_qty`), min(`l4_qty_nxt_lvl`),
                                   if( min(`l5_uom_full`) is null OR trim(min(`l5_uom_full`)) = \'\', min(`l5_uom_short`), min(`l5_uom_full`) ), 
                                        if( min(`l5_uom_full_p`) is null OR trim(min(`l5_uom_full_p`)) = \'\', min(`l5_uom_short_p`), min(`l5_uom_full_p`) ),
                                        min(`l5_qty`), min(`l5_qty_nxt_lvl`),
                                   if( min(`l6_uom_full`) is null OR trim(min(`l6_uom_full`)) = \'\', min(`l6_uom_short`), min(`l6_uom_full`) ), 
                                        if( min(`l6_uom_full_p`) is null OR trim(min(`l6_uom_full_p`)) = \'\', min(`l6_uom_short_p`), min(`l6_uom_full_p`) ),
                                        min(`l6_qty`), min(`l6_qty_nxt_lvl`),
                                   if( min(`l7_uom_full`) is null OR trim(min(`l7_uom_full`)) = \'\', min(`l7_uom_short`), min(`l7_uom_full`) ), 
                                        if( min(`l7_uom_full_p`) is null OR trim(min(`l7_uom_full_p`)) = \'\', min(`l7_uom_short_p`), min(`l7_uom_full_p`) ),
                                        min(`l7_qty`), min(`l7_qty_nxt_lvl`),
                                   if( min(`l8_uom_full`) is null OR trim(min(`l8_uom_full`)) = \'\', min(`l8_uom_short`), min(`l8_uom_full`) ), 
                                        if( min(`l8_uom_full_p`) is null OR trim(min(`l8_uom_full_p`)) = \'\', min(`l8_uom_short_p`), min(`l8_uom_full_p`) ),
                                        min(`l8_qty`), min(`l8_qty_nxt_lvl`),
                                   if( min(`l9_uom_full`) is null OR trim(min(`l9_uom_full`)) = \'\', min(`l9_uom_short`), min(`l9_uom_full`) ), 
                                        if( min(`l9_uom_full_p`) is null OR trim(min(`l9_uom_full_p`)) = \'\', min(`l9_uom_short_p`), min(`l9_uom_full_p`) ),
                                        min(`l9_qty`), min(`l9_qty_nxt_lvl`)) as uom';
        
                    $group_by .= ', prod_uom_id';
                } else { // short name
                    $select_col .= ', prod_uom_id, ' .$wpdb->prefix .'myi_udf_get_display_uom( sum(a.smallest_uom_qty),
                                   min(`l-9_uom_short`), min(`l-9_uom_short_p`), min(`l-9_qty`), min(`l-9_qty_nxt_lvl`),
                                   min(`l-8_uom_short`), min(`l-8_uom_short_p`), min(`l-8_qty`), min(`l-8_qty_nxt_lvl`),
                                   min(`l-7_uom_short`), min(`l-7_uom_short_p`), min(`l-7_qty`), min(`l-7_qty_nxt_lvl`),
                                   min(`l-6_uom_short`), min(`l-6_uom_short_p`), min(`l-6_qty`), min(`l-6_qty_nxt_lvl`),
                                   min(`l-5_uom_short`), min(`l-5_uom_short_p`), min(`l-5_qty`), min(`l-5_qty_nxt_lvl`),
                                   min(`l-4_uom_short`), min(`l-4_uom_short_p`), min(`l-4_qty`), min(`l-4_qty_nxt_lvl`),
                                   min(`l-3_uom_short`), min(`l-3_uom_short_p`), min(`l-3_qty`), min(`l-3_qty_nxt_lvl`),
                                   min(`l-2_uom_short`), min(`l-2_uom_short_p`), min(`l-2_qty`), min(`l-2_qty_nxt_lvl`),
                                   min(`l-1_uom_short`), min(`l-1_uom_short_p`), min(`l-1_qty`), min(`l-1_qty_nxt_lvl`),
                                   min(`l0_uom_short`), min(`l0_uom_short_p`), min(`l0_qty`), min(`l0_qty_nxt_lvl`),
                                   min(`l1_uom_short`), min(`l1_uom_short_p`), min(`l1_qty`), min(`l1_qty_nxt_lvl`),
                                   min(`l2_uom_short`), min(`l2_uom_short_p`), min(`l2_qty`), min(`l2_qty_nxt_lvl`),
                                   min(`l3_uom_short`), min(`l3_uom_short_p`), min(`l3_qty`), min(`l3_qty_nxt_lvl`),
                                   min(`l4_uom_short`), min(`l4_uom_short_p`), min(`l4_qty`), min(`l4_qty_nxt_lvl`),
                                   min(`l5_uom_short`), min(`l5_uom_short_p`), min(`l5_qty`), min(`l5_qty_nxt_lvl`),
                                   min(`l6_uom_short`), min(`l6_uom_short_p`), min(`l6_qty`), min(`l6_qty_nxt_lvl`),
                                   min(`l7_uom_short`), min(`l7_uom_short_p`), min(`l7_qty`), min(`l7_qty_nxt_lvl`),
                                   min(`l8_uom_short`), min(`l8_uom_short_p`), min(`l8_qty`), min(`l8_qty_nxt_lvl`),
                                   min(`l9_uom_short`), min(`l9_uom_short_p`), min(`l9_qty`), min(`l9_qty_nxt_lvl`)) as uom';
        
                    $group_by .= ', prod_uom_id';
                }
            } // display all UOM


            $select_body =' FROM `' .$wpdb->prefix .'myi_vw_inv_uoms` a 
                        INNER JOIN `' .$wpdb->prefix .'users` c 
                            on a.create_by_id = c.id 
                        LEFT JOIN `' .$wpdb->prefix .'myi_mst_location` d
                            on a.location_id = d.location_id
                        LEFT JOIN `' .$wpdb->prefix .'myi_mst_client` e /* client for */
                            on a.client_id_for = e.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_client` f /* client */
                            on a.client_id = f.client_id
                        INNER JOIN `' .$wpdb->prefix .'myi_mst_product` g
                            on a.prod_id = g.prod_id
                        INNER JOIN (    SELECT max(`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_user_client_role` a 
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` b 
                                            on a.role_id = b.role_id
                                            and a.deleted = 0
                                        WHERE a.user_id = %d
                                        group by a.client_id
                                        union
                                        /* get all the other clients that dun have records and use settings for client_id = 1 */
                                        SELECT max(d.`view_logs`) as `view_logs`, a.client_id
                                        FROM `' .$wpdb->prefix .'myi_mst_client` a 
                                        left join `' .$wpdb->prefix .'myi_mst_user_client_role` b 
                                            on a.client_id = b.client_id
                                            and a.deleted = 0
                                            and b.deleted = 0
                                            and b.user_id = %d
                                        inner join ( select *
                                                     from `' .$wpdb->prefix .'myi_mst_user_client_role` 
                                                     where client_id = 1
                                                        and user_id = %d
                                                        and deleted = 0 ) c 
                                            on c.client_id = 1
                                        inner join `' .$wpdb->prefix .'myi_mst_roles` d 
                                            on c.role_id = d.role_id
                                        WHERE b.role_id is null
                                        group by a.client_id ) cc 
                            on cc.view_logs = 1
                              and cc.client_id = a.client_id';

            $where = ' WHERE ';

            // check for date
            if ( $date === null ) {
                $where .= ' if(1=1, true, a.create_date <= %s)';
                $date = '';
            } else {
                $where .= ' a.create_date <= %s';
            }

            // client           
            if ( $client === null || count($client) == 0 || $client[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id in ( 0';

                foreach ($client as $id) {
                    $where .= ',' .$id;
                }

                $where .= ') ';
            }

            // prod
            if ( $prod === null || count($prod) == 0 || $prod[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.prod_id in ( 0';

                foreach ($prod as $id) {
                    $where .= ',' .$id;
                }

                $where .= ') ';
            }

            // loc
            if ( $loc === null || count($loc) == 0 || $loc[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.location_id in ( 0';

                foreach ($loc as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }

            // client_for
            if ( $client_for === null || count($client_for) == 0 || $client_for[0] == 0 ) {
                $where .= ' ';
            } else {
                $where .= ' and a.client_id_for in ( 0';

                foreach ($client_for as $id) {
                    $where .= ',' .$id;
                }
                
                $where .= ') ';
            }
            
            $order_by = ' ORDER BY f.client_cd, g.prod_cd';
            
            $outer_join = ' ) aa ';
            
            if (!( isset( $display_field ) && in_array( '14', $display_field ) )) { // if show zero count not checked
                $outer_join .= ' WHERE ifnull(aa.smallest_uom_qty,0) <> 0';
            }
            
            $stmt = $select_col .$select_body .$where .$group_by .$order_by .$outer_join;

            
            $my_result =  $wpdb->get_results( $wpdb->prepare(   $stmt,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $caller_user_id,
                                                                $date ));  

            if ( count( $my_result) <= 0 ) {
                return false;
            } else {
                return $my_result;
            }
        } // get_stock_count
    } // end class
}  else {
    throw new \Exception('Class \\my_inventory\\Myi_Report already exists. Action aborted...'); 
} // Myi_Report
?>