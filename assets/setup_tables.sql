/* Separate each command by @_new_command_@. Use @ for @_ or _@*/
/* use @_ignore_@ to not check for error for this statement and this statement fails, will not raise any error for next statement.
   meant originally for the CREATE TABLE command before ALTER TABLE*/
/* Log table not required for roles as it is non critical in accurracy */
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_roles` (
  `role_id` varchar(50) NOT NULL COMMENT 'Same as wp role',
  `view_inventory` tinyint(1) DEFAULT 0 NOT NULL,
  `stock_mod_inventory` tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Can stock in/out inventory',
  `create_inventory` tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Not in use atm',
  `mod_inventory` tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Not in use atm',
  `delete_inventory` tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Not in use atm',
  `view_inventory_master` tinyint(1) DEFAULT 0 NOT NULL,
  `create_inventory_master` tinyint(1) DEFAULT 0 NOT NULL,  
  `mod_inventory_master` tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Modify inventory master',
  `delete_inventory_master` tinyint(1) DEFAULT 0 NOT NULL,
  `create_user` tinyint(1) DEFAULT 0 NOT NULL,
  `mod_user` tinyint(1) DEFAULT 0 NOT NULL,
  `delete_user` tinyint(1) DEFAULT 0 NOT NULL,
  `mod_roles` tinyint(1) DEFAULT 0 NOT NULL,
  `view_logs` tinyint(1) DEFAULT 0 NOT NULL,
  `view_reports` tinyint(1) DEFAULT 0 NOT NULL,
  `wp_role` varchar(100) DEFAULT 'subscriber',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

@new_command@
INSERT IGNORE INTO `@wp_@myi_mst_roles` (  `role_id`, `wp_role`, `view_inventory`, `stock_mod_inventory`, `create_inventory`, `mod_inventory`, `delete_inventory`, 
                                    `mod_inventory_master`, `create_inventory_master`, `delete_inventory_master`, `create_user`, `mod_user`, `delete_user`, 
                                    `mod_roles`, `view_inventory_master`, `view_logs`, `view_reports`) VALUES
('myi_client', 'subscriber', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('myi_senior_staff', 'subscriber', 1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, NULL, NULL,1, 1, 1),
('myi_staff', 'subscriber', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,NULL,1, 1),
('myi_store_manager', 'myi_user_manage', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,1,1,1),
('myi_no_access', 'subscriber', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,NULL) ;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_client` (
  `client_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_cd` varchar(30) NOT NULL,
  `client_name` varchar(300) DEFAULT NULL,
  `client_remark` varchar(4000) DEFAULT NULL,
  `client_address` varchar(4000) DEFAULT NULL,
  `client_address2` varchar(4000) DEFAULT NULL,
  `client_address3` varchar(4000) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `uk_mst_client_client_cd` (`client_cd`,`deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@
INSERT IGNORE INTO `@wp_@myi_mst_client` (`client_id`, `client_cd`, `client_name`, `client_remark`, `deleted`, `create_date`, `create_by_id`, `last_mod_date`, `last_mod_by_id`, `delete_date`, `delete_by_id`) 
VALUES (NULL, 'Default', 'Default', NULL, 0, now(), @cur_user_id@, now(), @cur_user_id@, NULL, NULL);

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_client` like `@wp_@myi_mst_client`;

@new_command@
ALTER TABLE `@wp_@myi_logm_client` 
    CHANGE `client_id` `client_id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_mst_client_client_cd`,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,    
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST,
    ADD INDEX `idx_logm_client_key1` (`client_id`, `create_date` );

@new_command@
/* Note that client_id 1 (Ngai Heng) will mean that the person can access all client_id */
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_user_client_role` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `client_id` int(11) UNSIGNED NOT NULL,
    `role_id` varchar(50) NOT NULL,
    `deleted` int(11) NOT NULL DEFAULT '0',
    `create_date` datetime NOT NULL,
    `create_by_id` int(11) NOT NULL,
    `delete_date` datetime DEFAULT NULL,
    `delete_by_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`client_id`) REFERENCES `@wp_@myi_mst_client`(`client_id`),
    FOREIGN KEY (`role_id`) REFERENCES `@wp_@myi_mst_roles`(`role_id`),
    UNIQUE KEY `uk_mst_user_client_role_key1` (`user_id`,`client_id`,`role_id`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_location` (
  `location_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `location_cd` varchar(30) NOT NULL,
  `location_name` varchar(300) DEFAULT NULL,
  `location_desc` varchar(300),  
  `location_remark` varchar(4000) DEFAULT NULL,
  `add-field1` varchar(300),
  `add-field2` varchar(300),
  `add-field3` varchar(300),
  `add-field4` varchar(300),
  `add-field5` varchar(300),   
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `uk_mst_location_key1` (`location_cd`, `deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@
insert IGNORE into `@wp_@myi_mst_location` (`location_cd`,`create_date`,`create_by_id`,`last_mod_date`,`last_mod_by_id`)
values ('Dummy', now(), 1, now(), 1);

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_location` like `@wp_@myi_mst_location`;

@new_command@
ALTER TABLE `@wp_@myi_logm_location` 
    CHANGE `location_id` `location_id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_mst_location_key1`,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    ADD INDEX `idx_logm_location_key1` (`location_id`, `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_uom` (
  `uom_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uom_shortname` varchar(30) NOT NULL,
  `uom_shortname_p` varchar(30) NOT NULL COMMENT 'Plural',
  `uom_fullname` varchar(300) DEFAULT NULL,
  `uom_fullname_p` varchar(300) DEFAULT NULL COMMENT 'Plural',
  `uom_remark` varchar(4000) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`uom_id`),
  UNIQUE KEY `uk_mst_uom_key1`(`uom_shortname`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_uom` like `@wp_@myi_mst_uom`;

@new_command@
ALTER TABLE `@wp_@myi_logm_uom` 
    CHANGE `uom_id` `uom_id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_mst_uom_key1`,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    ADD INDEX `idx_logm_uom_key1` (`uom_id`, `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_product` (
  `prod_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prod_cd` varchar(30) NOT NULL,
  `prod_name` varchar(100),
  `prod_desc` varchar(300),
  `prod_dimension` varchar(300),
  `prod_img_url` varchar(300),
  `prod_remark` varchar(4000) DEFAULT NULL,
  `logo_lang` varchar(300) DEFAULT NULL,
  `add-field1` varchar(300),
  `add-field2` varchar(300),
  `add-field3` varchar(300),
  `add-field4` varchar(300),
  `add-field5` varchar(300),  
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`prod_id`),
  UNIQUE KEY `uk_mst_product_key1`(`prod_cd`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_product` like `@wp_@myi_mst_product`;

@new_command@
ALTER TABLE `@wp_@myi_logm_product`  
    CHANGE `prod_id` `prod_id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_mst_product_key1`,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    ADD INDEX `idx_logm_product_key1` (`prod_id`, `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_product_uom` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` int(11) UNSIGNED NOT NULL,
  `prod_id` int(11) UNSIGNED NOT NULL,  
  `desc` varchar(300),  
  `uom_level_-9_id` int(11),
  `l-9_qty` double default 0 comment 'level -9 1 UOM equal to smallest UOM qty',
  `l-9_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-8_id` int(11),
  `l-8_qty` double default 0 comment 'level -8 1 UOM equal to smallest UOM qty',
  `l-8_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',  
  `uom_level_-7_id` int(11),
  `l-7_qty` double default 0 comment 'level -7 1 UOM equal to smallest UOM qty',
  `l-7_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-6_id` int(11),
  `l-6_qty` double default 0 comment 'level -6 1 UOM equal to smallest UOM qty',
  `l-6_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-5_id` int(11),
  `l-5_qty` double default 0 comment 'level -5 1 UOM equal to smallest UOM qty',  
  `l-5_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-4_id` int(11),
  `l-4_qty` double default 0 comment 'level -4 1 UOM equal to smallest UOM qty',
  `l-4_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-3_id` int(11),
  `l-3_qty` double default 0 comment 'level -3 1 UOM equal to smallest UOM qty',  
  `l-3_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-2_id` int(11),
  `l-2_qty` double default 0 comment 'level -2 1 UOM equal to smallest UOM qty',  
  `l-2_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_-1_id` int(11),
  `l-1_qty` double default 0 comment 'level -1 1 UOM equal to smallest UOM qty',  
  `l-1_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_0_id` int(11) NOT NULL,
  `l0_qty` double default 1 comment 'level 0 1 UOM equal to smallest UOM qty',  
  `l0_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_1_id` int(11),
  `l1_qty` double default 0 comment 'level 1 1 UOM equal to smallest UOM qty',    
  `l1_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_2_id` int(11),
  `l2_qty` double default 0 comment 'level 2 1 UOM equal to smallest UOM qty',    
  `l2_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_3_id` int(11),
  `l3_qty` double default 0 comment 'level 3 1 UOM equal to smallest UOM qty',    
  `l3_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_4_id` int(11),
  `l4_qty` double default 0 comment 'level 4 1 UOM equal to smallest UOM qty',  
  `l4_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_5_id` int(11),
  `l5_qty` double default 0 comment 'level 5 1 UOM equal to smallest UOM qty',  
  `l5_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_6_id` int(11),
  `l6_qty` double default 0 comment 'level 6 1 UOM equal to smallest UOM qty',  
  `l6_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_7_id` int(11),
  `l7_qty` double default 0 comment 'level 7 1 UOM equal to smallest UOM qty',  
  `l7_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_8_id` int(11),
  `l8_qty` double default 0 comment 'level 8 1 UOM equal to smallest UOM qty',  
  `l8_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM',
  `uom_level_9_id` int(11),
  `l9_qty` double default 0 comment 'level 9 1 UOM equal to smallest UOM qty',  
  `l9_qty_nxt_lvl` double default 0 comment 'qty of this UOM in the next lower UOM. Shall always be 0 as no lower UOM',
  `uom_default_level` int(11) default 0,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`client_id`) REFERENCES `@wp_@myi_mst_client`(client_id),
  FOREIGN KEY (`prod_id`) REFERENCES `@wp_@myi_mst_product`(prod_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_product_uom` like `@wp_@myi_mst_product_uom`;

@new_command@
ALTER TABLE `@wp_@myi_logm_product_uom`  
    CHANGE `id` `id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    DROP COLUMN `l-9_qty`,
    DROP COLUMN `l-8_qty`,
    DROP COLUMN `l-7_qty`,
    DROP COLUMN `l-6_qty`,
    DROP COLUMN `l-5_qty`,
    DROP COLUMN `l-4_qty`,
    DROP COLUMN `l-3_qty`,
    DROP COLUMN `l-2_qty`,
    DROP COLUMN `l-1_qty`,
    DROP COLUMN `l0_qty`,
    DROP COLUMN `l1_qty`,
    DROP COLUMN `l2_qty`,
    DROP COLUMN `l3_qty`,
    DROP COLUMN `l4_qty`,
    DROP COLUMN `l5_qty`,
    DROP COLUMN `l6_qty`,
    DROP COLUMN `l7_qty`,
    DROP COLUMN `l8_qty`,
    DROP COLUMN `l9_qty`,
    ADD INDEX `idx_logm_product_key1` (`id`, `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_category` (
  `cat_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_cd` varchar(30) NOT NULL comment 'Category',
  `cat_name` varchar(300),
  `cat_img_url` varchar(300),  
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `last_mod_date` datetime NOT NULL,
  `last_mod_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `uk_mst_category_key1`(`cat_cd` ,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_category` like `@wp_@myi_mst_category`;

@new_command@
ALTER TABLE `@wp_@myi_logm_category` 
    CHANGE `cat_id` `cat_id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_mst_category_key1`,
    DROP COLUMN `last_mod_date`,
    DROP COLUMN `last_mod_by_id`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    ADD INDEX `idx_logm_category_key1` (`cat_id`, `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_mst_category_product` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` int(11) UNSIGNED NOT NULL,
  `cat_id` int(11) UNSIGNED NOT NULL,
  `prod_id` int(11) UNSIGNED NOT NULL,  
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  `delete_date` datetime DEFAULT NULL,
  `delete_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_txt_cat_prod_key1`(`client_id`, `cat_id`, `prod_id`,`deleted`),
  FOREIGN KEY (`client_id`) REFERENCES `@wp_@myi_mst_client`(client_id),
  FOREIGN KEY (`cat_id`)  REFERENCES `@wp_@myi_mst_category`(cat_id),
  FOREIGN KEY (`prod_id`) REFERENCES `@wp_@myi_mst_product`(prod_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@@ignore@
CREATE TABLE `@wp_@myi_logm_category_product` like `@wp_@myi_mst_category_product`;

@new_command@
ALTER TABLE `@wp_@myi_logm_category_product` 
    CHANGE `id` `id` int(11) NOT NULL,
    DROP PRIMARY KEY,
    DROP INDEX `uk_txt_cat_prod_key1`,
    DROP COLUMN `delete_date`,
    DROP COLUMN `delete_by_id`,
    DROP COLUMN `deleted`,
    ADD INDEX `idx_logt_cat_prod_key1` (`client_id`, `prod_id` , `create_date`),
    ADD INDEX `idx_logt_cat_prod_key2` (`client_id`, `cat_id` , `create_date`),
    ADD COLUMN `action` varchar(30) NOT NULL COMMENT 'Create, Update, Delete' FIRST;

@new_command@
CREATE TABLE IF NOT EXISTS `@wp_@myi_txt_inventory` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prod_uom_id` int(11) UNSIGNED NOT NULL,
  `location_id` int(11) UNSIGNED,
  `client_id_for` int(11) UNSIGNED COMMENT 'Meant for producing products for this client_id',
  `qty-l-9` double default 0,
  `qty-l-8` double default 0,
  `qty-l-7` double default 0,
  `qty-l-6` double default 0,
  `qty-l-5` double default 0,  
  `qty-l-4` double default 0,
  `qty-l-3` double default 0,
  `qty-l-2` double default 0,
  `qty-l-1` double default 0,
  `qty-l0` double default 0,
  `qty-l1` double default 0,
  `qty-l2` double default 0,
  `qty-l3` double default 0,
  `qty-l4` double default 0,
  `qty-l5` double default 0,
  `qty-l6` double default 0,
  `qty-l7` double default 0,
  `qty-l8` double default 0,
  `qty-l9` double default 0,  
  `job_no` varchar(300),
  `remarks` varchar(4000),
  `add-field1` varchar(300),
  `add-field2` varchar(300),
  `add-field3` varchar(300),
  `add-field4` varchar(300),
  `add-field5` varchar(300),
  `deleted` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `create_by_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`prod_uom_id`) REFERENCES `@wp_@myi_mst_product_uom`(`id`),
  FOREIGN KEY (`client_id_for`) REFERENCES `@wp_@myi_mst_client`(client_id),
  FOREIGN KEY (`location_id`) REFERENCES `@wp_@myi_mst_location`(location_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

@new_command@
/* purpose of below view is to reduce the amount of codings to get the uoms and thus will not link to location, clients etc.
   this will helps in performance in case the location and clients are not been used */
CREATE OR REPLACE VIEW `@wp_@myi_vw_inv_uoms` as
SELECT  a.id as inv_id,
        b.id as prod_uom_id,
        b.client_id,
        b.prod_id,
        b.`desc`,
        a.location_id,
        a.client_id_for,
        a.job_no,
        a.remarks,
        a.`add-field1`,
        a.`add-field2`,
        a.`add-field3`,
        a.`add-field4`,
        a.`add-field5`,
        a.create_by_id,
        a.create_date,
        b.uom_default_level,
        a.`qty-l-9`,
        b.`uom_level_-9_id`,
        b.`l-9_qty`,
        b.`l-9_qty_nxt_lvl`,
        `l-9`.`uom_shortname` as `l-9_uom_short`,
        `l-9`.`uom_shortname_p` as `l-9_uom_short_p`,
        `l-9`.`uom_fullname` as `l-9_uom_full`,
        `l-9`.`uom_fullname_p` as `l-9_uom_full_p`,
        a.`qty-l-8`,
        b.`uom_level_-8_id`,
        b.`l-8_qty`,
        b.`l-8_qty_nxt_lvl`,
        `l-8`.`uom_shortname` as `l-8_uom_short`,
        `l-8`.`uom_shortname_p` as `l-8_uom_short_p`,
        `l-8`.`uom_fullname` as `l-8_uom_full`,
        `l-8`.`uom_fullname_p` as `l-8_uom_full_p`,
        a.`qty-l-7`,
        b.`uom_level_-7_id`,
        b.`l-7_qty`,
        b.`l-7_qty_nxt_lvl`,
        `l-7`.`uom_shortname` as `l-7_uom_short`,
        `l-7`.`uom_shortname_p` as `l-7_uom_short_p`,
        `l-7`.`uom_fullname` as `l-7_uom_full`,
        `l-7`.`uom_fullname_p` as `l-7_uom_full_p`,
        a.`qty-l-6`,
        b.`uom_level_-6_id`,
        b.`l-6_qty`,
        b.`l-6_qty_nxt_lvl`,
        `l-6`.`uom_shortname` as `l-6_uom_short`,
        `l-6`.`uom_shortname_p` as `l-6_uom_short_p`,
        `l-6`.`uom_fullname` as `l-6_uom_full`,
        `l-6`.`uom_fullname_p` as `l-6_uom_full_p`,
        a.`qty-l-5`,
        b.`uom_level_-5_id`,
        b.`l-5_qty`,
        b.`l-5_qty_nxt_lvl`,
        `l-5`.`uom_shortname` as `l-5_uom_short`,
        `l-5`.`uom_shortname_p` as `l-5_uom_short_p`,
        `l-5`.`uom_fullname` as `l-5_uom_full`,
        `l-5`.`uom_fullname_p` as `l-5_uom_full_p`,
        a.`qty-l-4`,
        b.`uom_level_-4_id`,
        b.`l-4_qty`,
        b.`l-4_qty_nxt_lvl`,
        `l-4`.`uom_shortname` as `l-4_uom_short`,
        `l-4`.`uom_shortname_p` as `l-4_uom_short_p`,
        `l-4`.`uom_fullname` as `l-4_uom_full`,
        `l-4`.`uom_fullname_p` as `l-4_uom_full_p`,
        a.`qty-l-3`,
        b.`uom_level_-3_id`,
        b.`l-3_qty`,
        b.`l-3_qty_nxt_lvl`,
        `l-3`.`uom_shortname` as `l-3_uom_short`,
        `l-3`.`uom_shortname_p` as `l-3_uom_short_p`,
        `l-3`.`uom_fullname` as `l-3_uom_full`,
        `l-3`.`uom_fullname_p` as `l-3_uom_full_p`,
        a.`qty-l-2`,
        b.`uom_level_-2_id`,
        b.`l-2_qty`,
        b.`l-2_qty_nxt_lvl`,
        `l-2`.`uom_shortname` as `l-2_uom_short`,
        `l-2`.`uom_shortname_p` as `l-2_uom_short_p`,
        `l-2`.`uom_fullname` as `l-2_uom_full`,
        `l-2`.`uom_fullname_p` as `l-2_uom_full_p`,
        a.`qty-l-1`,
        b.`uom_level_-1_id`,
        b.`l-1_qty`,
        b.`l-1_qty_nxt_lvl`,
        `l-1`.`uom_shortname` as `l-1_uom_short`,
        `l-1`.`uom_shortname_p` as `l-1_uom_short_p`,
        `l-1`.`uom_fullname` as `l-1_uom_full`,
        `l-1`.`uom_fullname_p` as `l-1_uom_full_p`,
        a.`qty-l0`,
        b.`uom_level_0_id`,
        b.`l0_qty`,
        b.`l0_qty_nxt_lvl`,
        `l0`.`uom_shortname` as `l0_uom_short`,
        `l0`.`uom_shortname_p` as `l0_uom_short_p`,
        `l0`.`uom_fullname` as `l0_uom_full`,
        `l0`.`uom_fullname_p` as `l0_uom_full_p`,
        a.`qty-l1`,
        b.`uom_level_1_id`,
        b.`l1_qty`,
        b.`l1_qty_nxt_lvl`,
        `l1`.`uom_shortname` as `l1_uom_short`,
        `l1`.`uom_shortname_p` as `l1_uom_short_p`,
        `l1`.`uom_fullname` as `l1_uom_full`,
        `l1`.`uom_fullname_p` as `l1_uom_full_p`,
        a.`qty-l2`,
        b.`uom_level_2_id`,
        b.`l2_qty`,
        b.`l2_qty_nxt_lvl`,
        `l2`.`uom_shortname` as `l2_uom_short`,
        `l2`.`uom_shortname_p` as `l2_uom_short_p`,
        `l2`.`uom_fullname` as `l2_uom_full`,
        `l2`.`uom_fullname_p` as `l2_uom_full_p`,
        a.`qty-l3`,
        b.`uom_level_3_id`,
        b.`l3_qty`,
        b.`l3_qty_nxt_lvl`,
        `l3`.`uom_shortname` as `l3_uom_short`,
        `l3`.`uom_shortname_p` as `l3_uom_short_p`,
        `l3`.`uom_fullname` as `l3_uom_full`,
        `l3`.`uom_fullname_p` as `l3_uom_full_p`,
        a.`qty-l4`,
        b.`uom_level_4_id`,
        b.`l4_qty`,
        b.`l4_qty_nxt_lvl`,
        `l4`.`uom_shortname` as `l4_uom_short`,
        `l4`.`uom_shortname_p` as `l4_uom_short_p`,
        `l4`.`uom_fullname` as `l4_uom_full`,
        `l4`.`uom_fullname_p` as `l4_uom_full_p`,
        a.`qty-l5`,
        b.`uom_level_5_id`,
        b.`l5_qty`,
        b.`l5_qty_nxt_lvl`,
        `l5`.`uom_shortname` as `l5_uom_short`,
        `l5`.`uom_shortname_p` as `l5_uom_short_p`,
        `l5`.`uom_fullname` as `l5_uom_full`,
        `l5`.`uom_fullname_p` as `l5_uom_full_p`,
        a.`qty-l6`,
        b.`uom_level_6_id`,
        b.`l6_qty`,
        b.`l6_qty_nxt_lvl`,
        `l6`.`uom_shortname` as `l6_uom_short`,
        `l6`.`uom_shortname_p` as `l6_uom_short_p`,
        `l6`.`uom_fullname` as `l6_uom_full`,
        `l6`.`uom_fullname_p` as `l6_uom_full_p`,
        a.`qty-l7`,
        b.`uom_level_7_id`,
        b.`l7_qty`,
        b.`l7_qty_nxt_lvl`,
        `l7`.`uom_shortname` as `l7_uom_short`,
        `l7`.`uom_shortname_p` as `l7_uom_short_p`,
        `l7`.`uom_fullname` as `l7_uom_full`,
        `l7`.`uom_fullname_p` as `l7_uom_full_p`,
        a.`qty-l8`,
        b.`uom_level_8_id`,
        b.`l8_qty`,
        b.`l8_qty_nxt_lvl`,
        `l8`.`uom_shortname` as `l8_uom_short`,
        `l8`.`uom_shortname_p` as `l8_uom_short_p`,
        `l8`.`uom_fullname` as `l8_uom_full`,
        `l8`.`uom_fullname_p` as `l8_uom_full_p`,
        a.`qty-l9`,
        b.`uom_level_9_id`,
        b.`l9_qty`,
        b.`l9_qty_nxt_lvl`,
        `l9`.`uom_shortname` as `l9_uom_short`,
        `l9`.`uom_shortname_p` as `l9_uom_short_p`,
        `l9`.`uom_fullname` as `l9_uom_full`,
        `l9`.`uom_fullname_p` as `l9_uom_full_p`,
        ifnull(a.`qty-l-9`, 0) * ifnull(b.`l-9_qty`, 0)  + ifnull(a.`qty-l-8`, 0) * ifnull(b.`l-8_qty`, 0) + ifnull(a.`qty-l-7`, 0) * ifnull(b.`l-7_qty`, 0)
        + ifnull(a.`qty-l-6`, 0) * ifnull(b.`l-6_qty`, 0) + ifnull(a.`qty-l-5`, 0) * ifnull(b.`l-5_qty`, 0) + ifnull(a.`qty-l-4`, 0) * ifnull(b.`l-4_qty`, 0)
        + ifnull(a.`qty-l-3`, 0) * ifnull(b.`l-3_qty`, 0) + ifnull(a.`qty-l-2`, 0) * ifnull(b.`l-2_qty`, 0) + ifnull(a.`qty-l-1`, 0) * ifnull(b.`l-1_qty`, 0)
        + ifnull(a.`qty-l0`, 0) * ifnull(b.`l0_qty`, 0) + ifnull(a.`qty-l1`, 0) * ifnull(b.`l1_qty`, 0) + ifnull(a.`qty-l2`, 0) * ifnull(b.`l2_qty`, 0)
        + ifnull(a.`qty-l3`, 0) * ifnull(b.`l3_qty`, 0) + ifnull(a.`qty-l4`, 0) * ifnull(b.`l4_qty`, 0) + ifnull(a.`qty-l5`, 0) * ifnull(b.`l5_qty`, 0)
        + ifnull(a.`qty-l6`, 0) * ifnull(b.`l6_qty`, 0) + ifnull(a.`qty-l7`, 0) * ifnull(b.`l7_qty`, 0) + ifnull(a.`qty-l8`, 0) * ifnull(b.`l8_qty`, 0)
        + ifnull(a.`qty-l9`, 0) * ifnull(b.`l9_qty`, 0) as smallest_uom_qty,
        `smallest_uom`.uom_id as `smallest_uom_id`,
        `smallest_uom`.`uom_shortname` as `smallest_uom_short`,
        `smallest_uom`.`uom_shortname_p` as `smallest_uom_short_p`,
        `smallest_uom`.`uom_fullname` as `smallest_uom_full`,
        `smallest_uom`.`uom_fullname_p` as `smallest_uom_full_p`
FROM `@wp_@myi_txt_inventory` a
INNER JOIN `@wp_@myi_mst_product_uom` b 
    ON a.`prod_uom_id` = b.`ID`
LEFT JOIN `@wp_@myi_mst_uom` `l-9`
    ON b.`uom_level_-9_id` = `l-9`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-8`
    ON b.`uom_level_-8_id` = `l-8`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-7`
    ON b.`uom_level_-7_id` = `l-7`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-6`
    ON b.`uom_level_-6_id` = `l-6`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-5`
    ON b.`uom_level_-5_id` = `l-5`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-4`
    ON b.`uom_level_-4_id` = `l-4`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-3`
    ON b.`uom_level_-3_id` = `l-3`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-2`
    ON b.`uom_level_-2_id` = `l-2`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-1`
    ON b.`uom_level_-1_id` = `l-1`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l0`
    ON b.`uom_level_0_id` = `l0`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l1`
    ON b.`uom_level_1_id` = `l1`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l2`
    ON b.`uom_level_2_id` = `l2`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l3`
    ON b.`uom_level_3_id` = `l3`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l4`
    ON b.`uom_level_4_id` = `l4`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l5`
    ON b.`uom_level_5_id` = `l5`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l6`
    ON b.`uom_level_6_id` = `l6`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l7`
    ON b.`uom_level_7_id` = `l7`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l8`
    ON b.`uom_level_8_id` = `l8`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l9`
    ON b.`uom_level_9_id` = `l9`.uom_id
INNER JOIN `@wp_@myi_mst_uom` `smallest_uom`
    ON `smallest_uom`.uom_id = COALESCE(b.`uom_level_9_id`,b.`uom_level_8_id`,b.`uom_level_7_id`,b.`uom_level_6_id`,b.`uom_level_5_id`,
                                        b.`uom_level_4_id`,b.`uom_level_3_id`,b.`uom_level_2_id`,b.`uom_level_1_id`,b.`uom_level_0_id`,
                                        b.`uom_level_-1_id`,b.`uom_level_-2_id`,b.`uom_level_-3_id`,b.`uom_level_-4_id`,b.`uom_level_-5_id`,
                                        b.`uom_level_-6_id`,b.`uom_level_-7_id`,b.`uom_level_-8_id`,b.`uom_level_-9_id`) 
WHERE a.deleted = 0;

@new_command@
DROP FUNCTION IF EXISTS @wp_@myi_udf_get_qty_uom;

@new_command@
CREATE FUNCTION @wp_@myi_udf_get_qty_uom( QTY double, UOM varchar(300) )
RETURNS varchar(400) DETERMINISTIC
BEGIN
    if isnull(QTY) || QTY = 0 then
        return '';
    else
        return concat(QTY,' ',UOM, ',');
    end if;
END;

@new_command@
DROP FUNCTION IF EXISTS @wp_@myi_udf_generate_display_uom;

@new_command@
CREATE FUNCTION @wp_@myi_udf_generate_display_uom( QTY DOUBLE, `UOM` VARCHAR( 300 ), `UOM_p` VARCHAR( 300 ), `UOM_Qty` DOUBLE, `UOM_nxt_qty` DOUBLE ) 
RETURNS VARCHAR( 400 ) DETERMINISTIC 
BEGIN 
    SET @new_display =  '';
/**/
    IF QTY > 1 THEN
            SET @new_display = CONCAT( @new_display ,  ' ', QTY,  ' ',  `UOM_p` );
    ELSE
            SET @new_display = CONCAT( @new_display ,  ' ', QTY,  ' ',  `UOM` );
    END IF;
/**/
    IF  `UOM_nxt_qty` >0 THEN 
            SET @new_display = CONCAT( @new_display ,  ' <- ( X ',  `UOM_nxt_qty` ,  ' ) <-' ) ;
    END IF ;
/**/
    RETURN @new_display ;
END ;

@new_command@
DROP FUNCTION IF EXISTS @wp_@myi_udf_get_display_uom;

@new_command@
CREATE FUNCTION @wp_@myi_udf_get_display_uom(   QTY double, 
                                                `UOM-9` varchar(300), `UOM-9_p` varchar(300), `UOM_Qty-9` double, `UOM_nxt_qty-9` double,
                                                `UOM-8` varchar(300), `UOM-8_p` varchar(300), `UOM_Qty-8` double, `UOM_nxt_qty-8` double,
                                                `UOM-7` varchar(300), `UOM-7_p` varchar(300), `UOM_Qty-7` double, `UOM_nxt_qty-7` double,
                                                `UOM-6` varchar(300), `UOM-6_p` varchar(300), `UOM_Qty-6` double, `UOM_nxt_qty-6` double,
                                                `UOM-5` varchar(300), `UOM-5_p` varchar(300), `UOM_Qty-5` double, `UOM_nxt_qty-5` double,
                                                `UOM-4` varchar(300), `UOM-4_p` varchar(300), `UOM_Qty-4` double, `UOM_nxt_qty-4` double,
                                                `UOM-3` varchar(300), `UOM-3_p` varchar(300), `UOM_Qty-3` double, `UOM_nxt_qty-3` double,
                                                `UOM-2` varchar(300), `UOM-2_p` varchar(300), `UOM_Qty-2` double, `UOM_nxt_qty-2` double,
                                                `UOM-1` varchar(300), `UOM-1_p` varchar(300), `UOM_Qty-1` double, `UOM_nxt_qty-1` double,
                                                `UOM0` varchar(300), `UOM0_p` varchar(300), `UOM_Qty0` double, `UOM_nxt_qty0` double,
                                                `UOM1` varchar(300), `UOM1_p` varchar(300), `UOM_Qty1` double, `UOM_nxt_qty1` double,
                                                `UOM2` varchar(300), `UOM2_p` varchar(300), `UOM_Qty2` double, `UOM_nxt_qty2` double,
                                                `UOM3` varchar(300), `UOM3_p` varchar(300), `UOM_Qty3` double, `UOM_nxt_qty3` double,
                                                `UOM4` varchar(300), `UOM4_p` varchar(300), `UOM_Qty4` double, `UOM_nxt_qty4` double,
                                                `UOM5` varchar(300), `UOM5_p` varchar(300), `UOM_Qty5` double, `UOM_nxt_qty5` double,
                                                `UOM6` varchar(300), `UOM6_p` varchar(300), `UOM_Qty6` double, `UOM_nxt_qty6` double,
                                                `UOM7` varchar(300), `UOM7_p` varchar(300), `UOM_Qty7` double, `UOM_nxt_qty7` double,
                                                `UOM8` varchar(300), `UOM8_p` varchar(300), `UOM_Qty8` double, `UOM_nxt_qty8` double,
                                                `UOM9` varchar(300), `UOM9_p` varchar(300), `UOM_Qty9` double, `UOM_nxt_qty9` double
                                            )
RETURNS varchar(4000) DETERMINISTIC
BEGIN
    if isnull(QTY) || QTY = 0 then
        return '';        
    end if;
/**/
    SET @display = '';
    SET @remain_qty = QTY;
    SET @cnt = -9;
/**/       
    if NOT ( isnull(`UOM-9`) || isnull(`UOM_Qty-9`) || `UOM_Qty-9` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-9`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-9`;
        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-9`, `UOM-9_p`, `UOM_Qty-9`, `UOM_nxt_qty-9` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-8`) || isnull(`UOM_Qty-8`) || `UOM_Qty-8` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-8`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-8`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-8`, `UOM-8_p`, `UOM_Qty-8`, `UOM_nxt_qty-8` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-7`) || isnull(`UOM_Qty-7`) || `UOM_Qty-7` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-7`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-7`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-7`, `UOM-7_p`, `UOM_Qty-7`, `UOM_nxt_qty-7` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-6`) || isnull(`UOM_Qty-6`) || `UOM_Qty-6` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-6`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-6`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-6`, `UOM-6_p`, `UOM_Qty-6`, `UOM_nxt_qty-6` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-5`) || isnull(`UOM_Qty-5`) || `UOM_Qty-5` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-5`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-5`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-5`, `UOM-5_p`, `UOM_Qty-5`, `UOM_nxt_qty-5` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-4`) || isnull(`UOM_Qty-4`) || `UOM_Qty-4` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-4`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-4`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-4`, `UOM-4_p`, `UOM_Qty-4`, `UOM_nxt_qty-4` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-3`) || isnull(`UOM_Qty-3`) || `UOM_Qty-3` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-3`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-3`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-3`, `UOM-3_p`, `UOM_Qty-3`, `UOM_nxt_qty-3` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-2`) || isnull(`UOM_Qty-2`) || `UOM_Qty-2` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-2`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-2`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-2`, `UOM-2_p`, `UOM_Qty-2`, `UOM_nxt_qty-2` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM-1`) || isnull(`UOM_Qty-1`) || `UOM_Qty-1` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty-1`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty-1`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM-1`, `UOM-1_p`, `UOM_Qty-1`, `UOM_nxt_qty-1` ) );
    end if;
/**/    
    if NOT ( isnull(`UOM0`) || isnull(`UOM_Qty0`) || `UOM_Qty0` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty0`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty0`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM0`, `UOM0_p`, `UOM_Qty0`, `UOM_nxt_qty0` ) );
    end if;
/**/
    if NOT ( isnull(`UOM1`) || isnull(`UOM_Qty1`) || `UOM_Qty1` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty1`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty1`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM1`, `UOM1_p`, `UOM_Qty1`, `UOM_nxt_qty1` ) );
    end if;
/**/
    if NOT ( isnull(`UOM2`) || isnull(`UOM_Qty2`) || `UOM_Qty2` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty2`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty2`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM2`, `UOM2_p`, `UOM_Qty2`, `UOM_nxt_qty2` ) );
    end if;
/**/
    if NOT ( isnull(`UOM3`) || isnull(`UOM_Qty3`) || `UOM_Qty3` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty3`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty3`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM3`, `UOM3_p`, `UOM_Qty3`, `UOM_nxt_qty3` ) );
    end if;
/**/
    if NOT ( isnull(`UOM4`) || isnull(`UOM_Qty4`) || `UOM_Qty4` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty4`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty4`;
/**/        
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM4`, `UOM4_p`, `UOM_Qty4`, `UOM_nxt_qty4` ) );
    end if;
/**/
    if NOT ( isnull(`UOM5`) || isnull(`UOM_Qty5`) || `UOM_Qty5` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty5`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty5`;
/**/
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM5`, `UOM5_p`, `UOM_Qty5`, `UOM_nxt_qty5` ) );
    end if;
/**/
    if NOT ( isnull(`UOM6`) || isnull(`UOM_Qty6`) || `UOM_Qty6` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty6`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty6`;
/**/
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM6`, `UOM6_p`, `UOM_Qty6`, `UOM_nxt_qty6` ) );
    end if;
/**/
    if NOT ( isnull(`UOM7`) || isnull(`UOM_Qty7`) || `UOM_Qty7` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty7`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty7`;
/**/
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM7`, `UOM7_p`, `UOM_Qty7`, `UOM_nxt_qty7` ) );
    end if;
/**/
    if NOT ( isnull(`UOM8`) || isnull(`UOM_Qty8`) || `UOM_Qty8` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty8`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty8`;
/**/
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM8`, `UOM8_p`, `UOM_Qty8`, `UOM_nxt_qty8` ) );
    end if;
/**/
    if NOT ( isnull(`UOM9`) || isnull(`UOM_Qty9`) || `UOM_Qty9` = 0 ) then
        SET @temp_qty = @remain_qty DIV `UOM_Qty9`;
        SET @remain_qty = @remain_qty - @temp_qty * `UOM_Qty9`;
/**/
        SET @display = concat( @display, @wp_@myi_udf_generate_display_uom( @temp_qty, `UOM9`, `UOM9_p`, `UOM_Qty9`, `UOM_nxt_qty9` ) );
    end if;
/**/
    RETURN @display;
END;

@new_command@
/* purpose of below view is to reduce the amount of codings to get the uoms. */
CREATE OR REPLACE VIEW `@wp_@myi_vw_log_prod_uoms` as
SELECT  b.id as prod_uom_id,
        b.create_by_id,
        b.create_date,
        b.action,
        b.client_id,
        b.prod_id,
        b.`desc`,
        case b.uom_default_level
            when -9 then `l-9`.`uom_shortname`
            when -8 then `l-8`.`uom_shortname`
            when -7 then `l-7`.`uom_shortname`
            when -6 then `l-6`.`uom_shortname`
            when -5 then `l-5`.`uom_shortname`
            when -4 then `l-4`.`uom_shortname`
            when -3 then `l-3`.`uom_shortname`
            when -2 then `l-2`.`uom_shortname`
            when -1 then `l-1`.`uom_shortname`
            when 0 then `l0`.`uom_shortname`
            when 1 then `l1`.`uom_shortname`
            when 2 then `l2`.`uom_shortname`
            when 3 then `l3`.`uom_shortname`
            when 4 then `l4`.`uom_shortname`
            when 5 then `l5`.`uom_shortname`
            when 6 then `l6`.`uom_shortname`
            when 7 then `l7`.`uom_shortname`
            when 8 then `l8`.`uom_shortname`
            when 9 then `l9`.`uom_shortname`
        end as uom_default_level,
        b.`uom_level_-9_id`,
        b.`l-9_qty_nxt_lvl`,
        `l-9`.`uom_shortname` as `l-9_uom_short`,
        `l-9`.`uom_shortname_p` as `l-9_uom_short_p`,
        `l-9`.`uom_fullname` as `l-9_uom_full`,
        `l-9`.`uom_fullname_p` as `l-9_uom_full_p`,
        b.`uom_level_-8_id`,
        b.`l-8_qty_nxt_lvl`,
        `l-8`.`uom_shortname` as `l-8_uom_short`,
        `l-8`.`uom_shortname_p` as `l-8_uom_short_p`,
        `l-8`.`uom_fullname` as `l-8_uom_full`,
        `l-8`.`uom_fullname_p` as `l-8_uom_full_p`,
        b.`uom_level_-7_id`,
        b.`l-7_qty_nxt_lvl`,
        `l-7`.`uom_shortname` as `l-7_uom_short`,
        `l-7`.`uom_shortname_p` as `l-7_uom_short_p`,
        `l-7`.`uom_fullname` as `l-7_uom_full`,
        `l-7`.`uom_fullname_p` as `l-7_uom_full_p`,
        b.`uom_level_-6_id`,
        b.`l-6_qty_nxt_lvl`,
        `l-6`.`uom_shortname` as `l-6_uom_short`,
        `l-6`.`uom_shortname_p` as `l-6_uom_short_p`,
        `l-6`.`uom_fullname` as `l-6_uom_full`,
        `l-6`.`uom_fullname_p` as `l-6_uom_full_p`,
        b.`uom_level_-5_id`,
        b.`l-5_qty_nxt_lvl`,
        `l-5`.`uom_shortname` as `l-5_uom_short`,
        `l-5`.`uom_shortname_p` as `l-5_uom_short_p`,
        `l-5`.`uom_fullname` as `l-5_uom_full`,
        `l-5`.`uom_fullname_p` as `l-5_uom_full_p`,
        b.`uom_level_-4_id`,
        b.`l-4_qty_nxt_lvl`,
        `l-4`.`uom_shortname` as `l-4_uom_short`,
        `l-4`.`uom_shortname_p` as `l-4_uom_short_p`,
        `l-4`.`uom_fullname` as `l-4_uom_full`,
        `l-4`.`uom_fullname_p` as `l-4_uom_full_p`,
        b.`uom_level_-3_id`,
        b.`l-3_qty_nxt_lvl`,
        `l-3`.`uom_shortname` as `l-3_uom_short`,
        `l-3`.`uom_shortname_p` as `l-3_uom_short_p`,
        `l-3`.`uom_fullname` as `l-3_uom_full`,
        `l-3`.`uom_fullname_p` as `l-3_uom_full_p`,
        b.`uom_level_-2_id`,
        b.`l-2_qty_nxt_lvl`,
        `l-2`.`uom_shortname` as `l-2_uom_short`,
        `l-2`.`uom_shortname_p` as `l-2_uom_short_p`,
        `l-2`.`uom_fullname` as `l-2_uom_full`,
        `l-2`.`uom_fullname_p` as `l-2_uom_full_p`,
        b.`uom_level_-1_id`,
        b.`l-1_qty_nxt_lvl`,
        `l-1`.`uom_shortname` as `l-1_uom_short`,
        `l-1`.`uom_shortname_p` as `l-1_uom_short_p`,
        `l-1`.`uom_fullname` as `l-1_uom_full`,
        `l-1`.`uom_fullname_p` as `l-1_uom_full_p`,
        b.`uom_level_0_id`,
        b.`l0_qty_nxt_lvl`,
        `l0`.`uom_shortname` as `l0_uom_short`,
        `l0`.`uom_shortname_p` as `l0_uom_short_p`,
        `l0`.`uom_fullname` as `l0_uom_full`,
        `l0`.`uom_fullname_p` as `l0_uom_full_p`,
        b.`uom_level_1_id`,
        b.`l1_qty_nxt_lvl`,
        `l1`.`uom_shortname` as `l1_uom_short`,
        `l1`.`uom_shortname_p` as `l1_uom_short_p`,
        `l1`.`uom_fullname` as `l1_uom_full`,
        `l1`.`uom_fullname_p` as `l1_uom_full_p`,
        b.`uom_level_2_id`,
        b.`l2_qty_nxt_lvl`,
        `l2`.`uom_shortname` as `l2_uom_short`,
        `l2`.`uom_shortname_p` as `l2_uom_short_p`,
        `l2`.`uom_fullname` as `l2_uom_full`,
        `l2`.`uom_fullname_p` as `l2_uom_full_p`,
        b.`uom_level_3_id`,
        b.`l3_qty_nxt_lvl`,
        `l3`.`uom_shortname` as `l3_uom_short`,
        `l3`.`uom_shortname_p` as `l3_uom_short_p`,
        `l3`.`uom_fullname` as `l3_uom_full`,
        `l3`.`uom_fullname_p` as `l3_uom_full_p`,
        b.`uom_level_4_id`,
        b.`l4_qty_nxt_lvl`,
        `l4`.`uom_shortname` as `l4_uom_short`,
        `l4`.`uom_shortname_p` as `l4_uom_short_p`,
        `l4`.`uom_fullname` as `l4_uom_full`,
        `l4`.`uom_fullname_p` as `l4_uom_full_p`,
        b.`uom_level_5_id`,
        b.`l5_qty_nxt_lvl`,
        `l5`.`uom_shortname` as `l5_uom_short`,
        `l5`.`uom_shortname_p` as `l5_uom_short_p`,
        `l5`.`uom_fullname` as `l5_uom_full`,
        `l5`.`uom_fullname_p` as `l5_uom_full_p`,
        b.`uom_level_6_id`,
        b.`l6_qty_nxt_lvl`,
        `l6`.`uom_shortname` as `l6_uom_short`,
        `l6`.`uom_shortname_p` as `l6_uom_short_p`,
        `l6`.`uom_fullname` as `l6_uom_full`,
        `l6`.`uom_fullname_p` as `l6_uom_full_p`,
        b.`uom_level_7_id`,
        b.`l7_qty_nxt_lvl`,
        `l7`.`uom_shortname` as `l7_uom_short`,
        `l7`.`uom_shortname_p` as `l7_uom_short_p`,
        `l7`.`uom_fullname` as `l7_uom_full`,
        `l7`.`uom_fullname_p` as `l7_uom_full_p`,
        b.`uom_level_8_id`,
        b.`l8_qty_nxt_lvl`,
        `l8`.`uom_shortname` as `l8_uom_short`,
        `l8`.`uom_shortname_p` as `l8_uom_short_p`,
        `l8`.`uom_fullname` as `l8_uom_full`,
        `l8`.`uom_fullname_p` as `l8_uom_full_p`,
        b.`uom_level_9_id`,
        b.`l9_qty_nxt_lvl`,
        `l9`.`uom_shortname` as `l9_uom_short`,
        `l9`.`uom_shortname_p` as `l9_uom_short_p`,
        `l9`.`uom_fullname` as `l9_uom_full`,
        `l9`.`uom_fullname_p` as `l9_uom_full_p`,
        concat( if( ifnull(`uom_level_-9_id`,0) = 0, '', concat(`l-9`.`uom_shortname`, ' <- ( X ' , `l-9_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-8_id`,0) = 0, '', concat(`l-8`.`uom_shortname`, ' <- ( X ' , `l-8_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-7_id`,0) = 0, '', concat(`l-7`.`uom_shortname`, ' <- ( X ' , `l-7_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-6_id`,0) = 0, '', concat(`l-6`.`uom_shortname`, ' <- ( X ' , `l-6_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-5_id`,0) = 0, '', concat(`l-5`.`uom_shortname`, ' <- ( X ' , `l-5_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-4_id`,0) = 0, '', concat(`l-4`.`uom_shortname`, ' <- ( X ' , `l-4_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-3_id`,0) = 0, '', concat(`l-3`.`uom_shortname`, ' <- ( X ' , `l-3_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-2_id`,0) = 0, '', concat(`l-2`.`uom_shortname`, ' <- ( X ' , `l-2_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_-1_id`,0) = 0, '', concat(`l-1`.`uom_shortname`, ' <- ( X ' , `l-1_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_0_id`,0) = 0, '', concat(`l0`.`uom_shortname`, ' <- ( X ' , `l0_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_1_id`,0) = 0, '', concat(`l1`.`uom_shortname`, ' <- ( X ' , `l1_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_2_id`,0) = 0, '', concat(`l2`.`uom_shortname`, ' <- ( X ' , `l2_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_3_id`,0) = 0, '', concat(`l3`.`uom_shortname`, ' <- ( X ' , `l3_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_4_id`,0) = 0, '', concat(`l4`.`uom_shortname`, ' <- ( X ' , `l4_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_5_id`,0) = 0, '', concat(`l5`.`uom_shortname`, ' <- ( X ' , `l5_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_6_id`,0) = 0, '', concat(`l6`.`uom_shortname`, ' <- ( X ' , `l6_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_7_id`,0) = 0, '', concat(`l7`.`uom_shortname`, ' <- ( X ' , `l7_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_8_id`,0) = 0, '', concat(`l8`.`uom_shortname`, ' <- ( X ' , `l8_qty_nxt_lvl`, ' ) <- ')),
                if( ifnull(`uom_level_9_id`,0) = 0, '', concat(`l9`.`uom_shortname`, ' <- ( X ' , `l9_qty_nxt_lvl`, ' ) <- '))) as prod_uom
FROM `@wp_@myi_logm_product_uom` b 
LEFT JOIN `@wp_@myi_mst_uom` `l-9`
    ON b.`uom_level_-9_id` = `l-9`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-8`
    ON b.`uom_level_-8_id` = `l-8`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-7`
    ON b.`uom_level_-7_id` = `l-7`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-6`
    ON b.`uom_level_-6_id` = `l-6`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-5`
    ON b.`uom_level_-5_id` = `l-5`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-4`
    ON b.`uom_level_-4_id` = `l-4`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-3`
    ON b.`uom_level_-3_id` = `l-3`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-2`
    ON b.`uom_level_-2_id` = `l-2`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l-1`
    ON b.`uom_level_-1_id` = `l-1`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l0`
    ON b.`uom_level_0_id` = `l0`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l1`
    ON b.`uom_level_1_id` = `l1`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l2`
    ON b.`uom_level_2_id` = `l2`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l3`
    ON b.`uom_level_3_id` = `l3`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l4`
    ON b.`uom_level_4_id` = `l4`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l5`
    ON b.`uom_level_5_id` = `l5`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l6`
    ON b.`uom_level_6_id` = `l6`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l7`
    ON b.`uom_level_7_id` = `l7`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l8`
    ON b.`uom_level_8_id` = `l8`.uom_id
LEFT JOIN `@wp_@myi_mst_uom` `l9`
    ON b.`uom_level_9_id` = `l9`.uom_id;
