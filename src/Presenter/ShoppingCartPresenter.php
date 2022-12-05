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

namespace WorldlineOP\PrestaShop\Presenter;

use Cart;

/**
 * Class ShoppingCartPresenter
 * @package WorldlineOP\PrestaShop\Presenter
 */
class ShoppingCartPresenter implements PresenterInterface
{
    /** @var Cart $cart */
    private $cart;

    /** @var mixed[] $products */
    private $products;

    /** @var mixed[] $cartRules */
    private $cartRules;

    /** @var float $discountShippingWithoutTax */
    private $discountShippingWithoutTax;

    /** @var float $discountShippingWithTax */
    private $discountShippingWithTax;

    /** @var float $discountProductsWithTax */
    private $discountProductsWithTax;

    /** @var float $orderDiscountPercent */
    private $orderDiscountPercent;

    /**
     * @param Cart|false $cart
     * @return array
     * @throws \PrestaShopException|\Exception
     */
    public function present($cart = false)
    {
        if (!$cart) {
            throw new \Exception('Cart is not valid');
        }
        $this->cart = $cart;
        $this->products = $cart->getProducts();
        $this->cartRules = $cart->getCartRules();
        $this->discountShippingWithoutTax = 0;
        $this->discountShippingWithTax = 0;
        $this->discountProductsWithTax = 0;
        $rows = [];
        $this->separateDiscount();
        $rows['shipping'] = $this->getShippingRow();
        $rows['products'] = $this->getProductRows();
        $rows['cart'] = $cart;
        $this->applyProductDiscounts($rows['products']);
        $this->fixTotalsRounding($rows['products']);
        $this->formatPrices($rows['products']);

        return $rows;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function separateDiscount()
    {
        $freeShipping = false;
        foreach ($this->cartRules as $cartRule) {
            if ($cartRule['free_shipping']) {
                $freeShipping = true;
            }
        }
        if ($freeShipping) {
            $this->discountShippingWithoutTax = $this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING);
            $this->discountShippingWithTax = $this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $this->discountProductsWithTax = $this->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS) - $this->discountShippingWithTax;
        } else {
            $this->discountProductsWithTax = $this->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        }
        $this->orderDiscountPercent = ((100 * $this->discountProductsWithTax) / $this->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS)) / 100;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getShippingRow()
    {
        $shippingWithTaxes = $this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $shippingWithoutTaxes = $this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING);

        return [
            'priceWithTax' => $this->discountShippingWithoutTax ? 0 : \Tools::ps_round($this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2) * 100,
            'priceWithoutTax' => \Tools::ps_round($this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING), 2) * 100,
            'discountPrice' => \Tools::ps_round($this->discountShippingWithoutTax, 2) * 100,
            'priceDiscountedWithoutTax' => \Tools::ps_round($this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING) - $this->discountShippingWithoutTax, 2) * 100,
            'tax' => $this->discountShippingWithoutTax ? 0 : \Tools::ps_round($shippingWithTaxes - $shippingWithoutTaxes, 2) * 100,
        ];
    }

    /**
     * @return array
     */
    private function getProductRows()
    {
        $rows = [];
        foreach ($this->products as $product) {
            $i = 0;
            while ($i < $product['quantity']) {
                $totalWithTax = \Tools::ps_round($product['price_with_reduction'], 2);
                $productPrice = \Tools::ps_round($product['price_with_reduction_without_tax'], 2);
                $row = [
                    'totalWithTax' => $totalWithTax,
                    'productPrice' => $productPrice,
                    'discountPrice' => 0,
                    'tax' => \Tools::ps_round($totalWithTax - $productPrice, 2),
                    'quantity' => 1,
                    'productCode' => $product['reference'] ?: $product['unique_id'],
                    'productName' => $product['name'],
                    'data' => $product,
                ];

                $rows[] = $row;
                $i++;
            }
        }

        return $rows;
    }

    /**
     * @param array $productRows
     * @return void
     * @throws \Exception
     */
    private function applyProductDiscounts(&$productRows)
    {
        if (!$this->discountProductsWithTax) {
            return;
        }
        foreach ($productRows as &$productRow) {
            $rate = $productRow['data']['rate'] / 100;
            $unitPriceDiscountedWithoutTax = $productRow['data']['price_with_reduction_without_tax'] - ($productRow['data']['price_with_reduction_without_tax'] * $this->orderDiscountPercent);
            $unitTaxAmountDiscounted = $unitPriceDiscountedWithoutTax * $rate;
            $discountAmountWithoutTax = $productRow['data']['price_with_reduction_without_tax'] - $unitPriceDiscountedWithoutTax;
            $totalPriceDiscountedWithTax = \Tools::ps_round($productRow['productPrice'], 2) - \Tools::ps_round($discountAmountWithoutTax, 2) + \Tools::ps_round($unitTaxAmountDiscounted, 2);

            $productRow['tax'] = \Tools::ps_round($unitTaxAmountDiscounted, 2);
            $productRow['discountPrice'] = \Tools::ps_round($discountAmountWithoutTax, 2);
            $productRow['totalWithTax'] = \Tools::ps_round($totalPriceDiscountedWithTax, 2);
        }
    }

    /**
     * @param array $productRows
     * @return void
     * @throws \Exception
     */
    private function fixTotalsRounding(&$productRows)
    {
        $totalCalculated = array_sum(array_map(function($row) {
            return $row['totalWithTax'];
        }, $productRows));
        $totalCart = $this->cart->getOrderTotal() - $this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING) + $this->discountShippingWithTax;
        if (abs($totalCalculated - $totalCart) < 0.001) {
            return;
        }
        $diff = \Tools::ps_round($totalCalculated - $totalCart, 2);
        $productRows[0]['totalWithTax'] -= $diff;
        $productRows[0]['productPrice'] -= $diff;
    }

    /**
     * @param array $productRows
     * @return void
     */
    private function formatPrices(&$productRows)
    {
        foreach ($productRows as &$productRow) {
            $productRow['totalWithTax'] *= 100;
            $productRow['productPrice'] *= 100;
            $productRow['discountPrice'] *= 100;
            $productRow['tax'] *= 100;
        }
    }
}
