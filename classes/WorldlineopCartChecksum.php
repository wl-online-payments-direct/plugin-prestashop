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
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class WorldlineopCartChecksum
 */
class WorldlineopCartChecksum implements ChecksumInterface
{
    public $addressChecksum;
    private $separator = '_';
    private $subseparator = '-';

    /**
     * @param AddressChecksum $addressChecksum
     */
    public function __construct(AddressChecksum $addressChecksum)
    {
        $this->addressChecksum = $addressChecksum;
    }

    /**
     * @param Cart $cart
     *
     * @return string
     */
    public function generateChecksum($cart)
    {
        $uniqId = '';
        $uniqId .= $cart->id_shop;
        $uniqId .= $this->separator;
        $uniqId .= $cart->id_customer;
        $uniqId .= $this->separator;
        $uniqId .= $cart->id_guest;
        $uniqId .= $this->separator;
        $uniqId .= $cart->id_currency;
        $uniqId .= $this->separator;
        $uniqId .= $cart->id_lang;
        $uniqId .= $this->separator;

        $uniqId .= $this->addressChecksum->generateChecksum(new Address($cart->id_address_delivery));
        $uniqId .= $this->separator;
        $uniqId .= $this->addressChecksum->generateChecksum(new Address($cart->id_address_invoice));
        $uniqId .= $this->separator;

        $products = $cart->getProducts($refresh = true);
        foreach ($products as $product) {
            $uniqId .= $product['id_shop']
                . $this->subseparator
                . $product['id_product']
                . $this->subseparator
                . $product['id_product_attribute']
                . $this->subseparator
                . $product['cart_quantity']
                . $this->subseparator
                . $product['total_wt'];
            $uniqId .= $this->separator;
        }

        $cartRules = $cart->getCartRules();
        foreach ($cartRules as $cartRule) {
            $uniqId .= $cartRule['id_cart_rule']
                . $this->subseparator
                . $cartRule['code']
                . $this->subseparator
                . $cartRule['minimum_amount']
                . $this->subseparator
                . $cartRule['country_restriction']
                . $this->subseparator
                . $cartRule['carrier_restriction']
                . $this->subseparator
                . $cartRule['group_restriction']
                . $this->subseparator
                . $cartRule['cart_rule_restriction']
                . $this->subseparator
                . $cartRule['product_restriction']
                . $this->subseparator
                . $cartRule['shop_restriction']
                . $this->subseparator
                . $cartRule['free_shipping']
                . $this->subseparator
                . $cartRule['reduction_percent']
                . $this->subseparator
                . $cartRule['reduction_amount']
                . $this->subseparator
                . $cartRule['reduction_tax']
                . $this->subseparator
                . $cartRule['gift_product']
                . $this->subseparator
                . $cartRule['gift_product_attribute']
                . $this->subseparator
                . $cartRule['active'];
            $uniqId .= $this->separator;
        }

        $uniqId .= $cart->id_carrier;
        $uniqId .= $this->subseparator;
        $uniqId .= $cart->getTotalShippingCost();
        $uniqId .= $this->subseparator;
        $uniqId .= $cart->id_address_delivery;
        $uniqId .= $this->subseparator;
        $uniqId .= $cart->getTotalWeight();
        $uniqId .= $this->separator;

        $uniqId = rtrim($uniqId, $this->separator);
        $uniqId = rtrim($uniqId, $this->subseparator);

        return sha1($uniqId);
    }
}
