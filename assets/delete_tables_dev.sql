@new_command@
SET FOREIGN_KEY_CHECKS = 0;

@new_command@
truncate table @wp_@myi_mst_roles;

@new_command@
truncate table @wp_@myi_mst_client;

@new_command@
truncate table @wp_@myi_logm_client;

@new_command@
truncate table @wp_@myi_mst_location;

@new_command@
truncate table @wp_@myi_logm_location;

@new_command@
truncate table @wp_@myi_mst_uom;

@new_command@
truncate table @wp_@myi_logm_uom;

@new_command@
truncate table @wp_@myi_mst_product;

@new_command@
truncate table @wp_@myi_logm_product;

@new_command@
truncate table @wp_@myi_mst_product_uom;

@new_command@
truncate table @wp_@myi_logm_product_uom;

@new_command@
truncate table @wp_@myi_mst_category;

@new_command@
truncate table @wp_@myi_logm_category;

@new_command@
truncate table @wp_@myi_mst_category_product;

@new_command@
truncate table @wp_@myi_logm_category_product;

@new_command@
truncate table @wp_@myi_txt_inventory;

@new_command@
truncate table @wp_@myi_mst_user_client_role;




@new_command@
drop table @wp_@myi_mst_roles;

@new_command@
drop table @wp_@myi_mst_client;

@new_command@
drop table @wp_@myi_logm_client;

@new_command@
drop table @wp_@myi_mst_location;

@new_command@
drop table @wp_@myi_logm_location;

@new_command@
drop table @wp_@myi_mst_uom;

@new_command@
drop table @wp_@myi_logm_uom;

@new_command@
drop table @wp_@myi_mst_product;

@new_command@
drop table @wp_@myi_logm_product;

@new_command@
drop table @wp_@myi_mst_product_uom;

@new_command@
drop table @wp_@myi_logm_product_uom;

@new_command@
drop table @wp_@myi_mst_category;

@new_command@
drop table @wp_@myi_logm_category;

@new_command@
drop table @wp_@myi_mst_category_product;

@new_command@
drop table @wp_@myi_logm_category_product;

@new_command@
drop table @wp_@myi_txt_inventory;

@new_command@
drop table @wp_@myi_mst_user_client_role;



@new_command@
drop view `@wp_@myi_vw_inv_uoms`;

@new_command@
drop function @wp_@myi_udf_get_qty_uom;

@new_command@
drop function @wp_@myi_udf_generate_display_uom;

@new_command@
drop function @wp_@myi_udf_get_display_uom;

@new_command@
drop view `@wp_@myi_vw_log_prod_uoms`;




@new_command@
SET FOREIGN_KEY_CHECKS = 1;
