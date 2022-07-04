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
 * Class HostedCheckout
 */
class HostedCheckout extends ObjectModel
{
    /** @var int $id_hosted_checkout */
    public $id_hosted_checkout;

    /** @var int $id_cart */
    public $id_cart;

    /** @var int $id_payment_product */
    public $id_payment_product;

    /** @var int $id_token */
    public $id_token;

    /** @var string $returnmac */
    public $returnmac;

    /** @var string $session_id */
    public $session_id;

    /** @var string $merchant_reference */
    public $merchant_reference;

    /** @var string $partial_redirect_url */
    public $partial_redirect_url;

    /** @var string $checksum */
    public $checksum;

    /** @var string $date_add */
    public $date_add;

    /** @var string $date_upd */
    public $date_upd;

    /** @var array $definition */
    public static $definition = [
        'table' => 'worldlineop_hosted_checkout',
        'primary' => 'id_hosted_checkout',
        'fields' => [
            'id_cart' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_payment_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
                'default' => 0,
            ],
            'id_token' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
                'default' => 0,
            ],
            'returnmac' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'session_id' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'merchant_reference' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'partial_redirect_url' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'checksum' => [
                'type' => self::TYPE_STRING,
                'allow_null' => false,
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];
}
