CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_hosted_checkout`
(
    `id_hosted_checkout`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart`              INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `id_payment_product`   INT(10) UNSIGNED NULL     DEFAULT '0',
    `id_token`             INT(10) UNSIGNED NULL     DEFAULT '0',
    `returnmac`            VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `session_id`           VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `merchant_reference`   VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `partial_redirect_url` VARCHAR(256)     NOT NULL COLLATE 'utf8mb4_general_ci',
    `checksum`             VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `date_add`             DATETIME         NOT NULL,
    `date_upd`             DATETIME         NOT NULL,
    PRIMARY KEY (`id_hosted_checkout`) USING BTREE,
    INDEX `id_cart` (`id_cart`) USING BTREE,
    INDEX `id_payment_product` (`id_payment_product`) USING BTREE,
    INDEX `id_token` (`id_token`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;
CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_transaction`
(
    `id_worldlineop_transaction` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order`                   INT(10) UNSIGNED NOT NULL,
    `reference`                  VARCHAR(50)      NOT NULL COLLATE 'utf8mb4_general_ci',
    `date_add`                   DATETIME         NOT NULL,
    PRIMARY KEY (`id_worldlineop_transaction`) USING BTREE,
    INDEX `id_order` (`id_order`) USING BTREE
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
;
CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_created_payment` (
	`id_created_payment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_cart` INT(11) NOT NULL,
	`payment_id` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`merchant_reference` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`returnmac` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`status` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`date_add` DATETIME NOT NULL,
	PRIMARY KEY (`id_created_payment`) USING BTREE,
	INDEX `payment_id` (`payment_id`) USING BTREE,
	INDEX `id_cart` (`id_cart`) USING BTREE
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB
;
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

CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_product_gift_card` (
    `id_product` INT(10) UNSIGNED NOT NULL,
    `product_type` ENUM('none','FoodAndDrink','HomeAndGarden','GiftAndFlowers') NOT NULL DEFAULT 'none' COLLATE 'utf8mb4_general_ci',
    UNIQUE INDEX `id_product` (`id_product`) USING BTREE
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB
;
