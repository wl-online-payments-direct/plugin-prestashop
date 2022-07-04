ALTER TABLE `PREFIX_worldlineop_transaction`
	DROP COLUMN `product_id`,
	DROP COLUMN `amount`;

CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_token` (
	`id_worldlineop_token` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_customer` INT(10) UNSIGNED NOT NULL,
	`id_shop` INT(10) UNSIGNED NOT NULL,
	`product_id` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`card_number` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`expiry_date` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`value` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`secure_key` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id_worldlineop_token`) USING BTREE,
	INDEX `id_customer` (`id_customer`) USING BTREE
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB
;
