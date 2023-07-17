CREATE TABLE IF NOT EXISTS `PREFIX_worldlineop_product_gift_card` (
    `id_product` INT(10) UNSIGNED NOT NULL,
    `product_type` ENUM('none','FoodAndDrink','HomeAndGarden','GiftAndFlowers') NOT NULL DEFAULT 'none' COLLATE 'utf8mb4_general_ci',
    UNIQUE INDEX `id_product` (`id_product`) USING BTREE
    )
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB
;
