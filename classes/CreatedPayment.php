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
 * Class CreatedPayment
 */
class CreatedPayment extends ObjectModel
{
    /** @var int $id_created_payment */
    public $id_created_payment;

    /** @var int $id_cart */
    public $id_cart;

    /** @var string $payment_id */
    public $payment_id;

    /** @var string $merchant_reference */
    public $merchant_reference;

    /** @var string $returnmac */
    public $returnmac;

    /** @var string $status */
    public $status;

    /** @var string $date_add */
    public $date_add;

    /** @var array $definition */
    public static $definition = [
        'table' => 'worldlineop_created_payment',
        'primary' => 'id_created_payment',
        'fields' => [
            'id_cart' => [
                'type' => self::TYPE_INT,
                'required' => true,
            ],
            'payment_id' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'merchant_reference' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'returnmac' => [
                'type' => self::TYPE_STRING,
                'allow_null' => true,
                'required' => false,
            ],
            'status' => [
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
