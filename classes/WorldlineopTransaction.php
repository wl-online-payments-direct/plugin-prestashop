<?php
/**
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop partner
 * @copyright 2021 Worldline Online Payments
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 */

/**
 * Class WorldlineopTransaction
 */
class WorldlineopTransaction extends ObjectModel
{
    /** @var int $id_worldlineop_transaction */
    public $id_worldlineop_transaction;

    /** @var int $id_order */
    public $id_order;

    /** @var string $reference */
    public $reference;

    /** @var string $date_add */
    public $date_add;

    /** @var array $definition */
    public static $definition = [
        'table' => 'worldlineop_transaction',
        'primary' => 'id_worldlineop_transaction',
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'required' => true,
            ],
            'reference' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];
}
