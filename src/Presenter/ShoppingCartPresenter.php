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

namespace WorldlineOP\PrestaShop\Presenter;

use Cart;
use WorldlineOP\PrestaShop\Builder\HostedPaymentRequestBuilder;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class ShoppingCartPresenter
 */
class ShoppingCartPresenter implements PresenterInterface
{
    /** @var Cart */
    private $cart;

    /** @var mixed[] */
    private $products;

    /** @var mixed[] */
    private $productsType;

    /** @var mixed[] */
    private $cartRules;

    /** @var float */
    private $discountShippingWithoutTax;

    /** @var float */
    private $discountShippingWithTax;

    /** @var float */
    private $discountProductsWithTax;

    /** @var float */
    private $orderDiscountPercent;

    /** @var string */
    private $cartCurrencyIso;

    /**
     * @param Cart|false $cart
     *
     * @return array
     *
     * @throws \PrestaShopException|\Exception
     */
    public function present($cart = false, $productId = null)
    {
        if (!$cart) {
            throw new \Exception('Cart is not valid');
        }
        $this->cart = $cart;
        $this->cartCurrencyIso = Tools::getIsoCurrencyCodeById($cart->id_currency);
        $this->products = $cart->getProducts();
        $this->cartRules = $cart->getCartRules();
        $this->discountShippingWithoutTax = 0;
        $this->discountShippingWithTax = 0;
        $this->discountProductsWithTax = 0;
        $rows = [];
        $this->separateDiscount();
        $this->assignProductsType();
        $rows['shipping'] = $this->getShippingRow();
        $rows['products'] = $productId === null ? $this->getProductRows() : $this->buildMergedProduct();
        $rows['cart'] = $cart;
        $this->applyProductDiscounts($rows['products']);
        $this->fixTotalsRounding($rows['products']);
        $this->formatPrices($rows['products']);

        return $rows;
    }

    /**
     * @return void
     *
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
     *
     * @throws \Exception
     */
    private function getShippingRow()
    {
        $shippingWithTaxes = $this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $shippingWithoutTaxes = $this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING);

        return [
            'priceWithTax' => $this->discountShippingWithoutTax ? 0 : Tools::getRoundedAmountInCents($this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), $this->cartCurrencyIso),
            'priceWithoutTax' => Tools::getRoundedAmountInCents($this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING), $this->cartCurrencyIso),
            'discountPrice' => Tools::getRoundedAmountInCents($this->discountShippingWithoutTax, $this->cartCurrencyIso),
            'priceDiscountedWithoutTax' => Tools::getRoundedAmountInCents($this->cart->getOrderTotal(false, Cart::ONLY_SHIPPING) - $this->discountShippingWithoutTax, $this->cartCurrencyIso),
            'tax' => $this->discountShippingWithoutTax ? 0 : Tools::getRoundedAmountInCents($shippingWithTaxes - $shippingWithoutTaxes, $this->cartCurrencyIso),
            'type' => $this->productsType['SHIPPING'],
        ];
    }

    /**
     * @return void
     */
    private function assignProductsType()
    {
        $types = [];
        foreach ($this->products as $product) {
            $type = Tools::getGiftCardTypeByIdProduct($product['id_product']);
            $types[$type][] = $product['id_product'];
        }

        $typeNone = isset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE]) ? $types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE] : [];
        unset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE]);
        if (count($types) > 1) {
            $types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE] = array_merge(
                isset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN]) ? $types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN] : [],
                isset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS]) ? $types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS] : [],
                $typeNone
            );
            $shippingTypeNone = true;
            unset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN]);
            unset($types[HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS]);
        } else {
            $shippingTypeNone = false;
        }
        $productsTypes = [];
        $productsTypes['SHIPPING'] = '';
        foreach ($types as $type => $ids) {
            foreach ($ids as $id) {
                $productsTypes[$id] = ($type == HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE ? '' : $type);
            }
            if ($type != HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE && false === $shippingTypeNone) {
                $productsTypes['SHIPPING'] = $type;
            }
        }

        $this->productsType = $productsTypes;
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
                $totalWithTax = Tools::getRoundedAmount($product['price_with_reduction'], $this->cartCurrencyIso);
                $productPrice = Tools::getRoundedAmount($product['price_with_reduction_without_tax'], $this->cartCurrencyIso);
                $row = [
                    'totalWithTax' => $totalWithTax,
                    'productPrice' => $productPrice,
                    'discountPrice' => 0,
                    'tax' => Tools::getRoundedAmount($totalWithTax - $productPrice, $this->cartCurrencyIso),
                    'quantity' => 1,
                    'productCode' => $product['reference'] ?: $product['unique_id'],
                    'productName' => $product['name'],
                    'productType' => !empty($this->productsType[$product['id_product']]) ? $this->productsType[$product['id_product']] : '',
                    'data' => $product,
                ];

                $rows[] = $row;
                ++$i;
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function buildMergedProduct()
    {
        $amounts = $this->getMergedProductAmounts($this->products);
        $productType = $this->getMergedProductType($this->products);
        $productName = $this->getMergedProductName($this->products);

        return array(
            array(
                'totalWithTax' => $amounts['productPrice'] + $amounts['tax'],
                'productPrice' => $amounts['productPrice'],
                'discountPrice' => $amounts['discountPrice'],
                'tax' => $amounts['tax'],
                'quantity' => 1,
                'productName' => $productName,
                'productType' => $productType,
                'productCode' => 'Merged item'
            ));
    }

    /**
     * @param array $products
     *
     * @return array
     */
    private function getMergedProductAmounts($products)
    {
        $productPrice = 0;
        $tax = 0;
        $totalWithTax = 0;

        foreach ($products as $product) {
            $totalWithTax += Tools::getRoundedAmount($product['price_with_reduction'], $this->cartCurrencyIso);
            $productPrice += Tools::getRoundedAmount($product['price_with_reduction_without_tax'], $this->cartCurrencyIso);
            $tax += Tools::getRoundedAmount($totalWithTax - $productPrice, $this->cartCurrencyIso);
        }

        return array(
            'discountPrice' => 0,
            'productPrice' => $productPrice,
            'tax' => $tax,
            'totalWithTax' => $totalWithTax
        );
    }

    /**
     * Determines the merged product type based on priority:
     * - FoodAndDrink > HomeAndGarden > GiftAndFlowers
     *
     * @param array $products
     * @return string
     */
    private function getMergedProductType($products)
    {
        $hasHomeAndGarden = false;

        foreach ($products as $product) {
            $type = Tools::getGiftCardTypeByIdProduct($product['id_product']);

            if ($type === HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_FOOD_DRINK) {
                return HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_FOOD_DRINK;
            }

            if ($type === HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN) {
                $hasHomeAndGarden = true;
            }
        }

        // If no FoodAndDrink but at least one HomeAndGarden
        if ($hasHomeAndGarden) {
            return HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN;
        }

        // Default fallback (GiftAndFlowers or others)
        return HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS;
    }

    /**
     * @param array $products
     *
     * @return string
     */
    private function getMergedProductName(array $products)
    {
        $typeCounts = [];
        $names = [];

        foreach ($products as $product) {
            $type = Tools::getGiftCardTypeByIdProduct($product['id_product']);
            if (!isset($typeCounts[$type])) {
                $typeCounts[$type] = 0;
            }
            $typeCounts[$type]++;
            $names[] = $product['name'];
        }

        // Create a string like "Product A + Product B + Product C"
        $nameString = implode(' + ', $names);

        if (mb_strlen($nameString) <= 50) {
            return $nameString;
        }

        $parts = [];
        foreach ($typeCounts as $type => $count) {
            $parts[] = "{$count} {$type}";
        }

        $result = implode(' & ', $parts);

        // Truncate if needed
        return mb_strlen($result) > 50 ? mb_substr($result, 0, 50) : $result;
    }

    /**
     * @param array $productRows
     *
     * @return void
     *
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
            $totalPriceDiscountedWithTax = Tools::getRoundedAmount($productRow['productPrice'], $this->cartCurrencyIso) - Tools::getRoundedAmount($discountAmountWithoutTax, $this->cartCurrencyIso) + Tools::getRoundedAmount($unitTaxAmountDiscounted, $this->cartCurrencyIso);

            $productRow['tax'] = Tools::getRoundedAmount($unitTaxAmountDiscounted, $this->cartCurrencyIso);
            $productRow['discountPrice'] = Tools::getRoundedAmount($discountAmountWithoutTax, $this->cartCurrencyIso);
            $productRow['totalWithTax'] = Tools::getRoundedAmount($totalPriceDiscountedWithTax, $this->cartCurrencyIso);
        }
    }

    /**
     * @param array $productRows
     *
     * @return void
     *
     * @throws \Exception
     */
    private function fixTotalsRounding(&$productRows)
    {
        $totalCalculated = array_sum(array_map(function ($row) {
            return $row['totalWithTax'];
        }, $productRows));
        $totalCart = $this->cart->getOrderTotal() - $this->cart->getOrderTotal(true, Cart::ONLY_SHIPPING) + $this->discountShippingWithTax;
        if (abs($totalCalculated - $totalCart) < 0.001) {
            return;
        }
        $diff = Tools::getRoundedAmount($totalCalculated - $totalCart, $this->cartCurrencyIso);
        $productRows[0]['totalWithTax'] -= $diff;
        $productRows[0]['productPrice'] -= $diff;
    }

    /**
     * @param array $productRows
     *
     * @return void
     */
    private function formatPrices(&$productRows)
    {
        foreach ($productRows as &$productRow) {
            $productRow['totalWithTax'] = Tools::getAmountInCents($productRow['totalWithTax'], $this->cartCurrencyIso);
            $productRow['productPrice'] = Tools::getAmountInCents($productRow['productPrice'], $this->cartCurrencyIso);
            $productRow['discountPrice'] = Tools::getAmountInCents($productRow['discountPrice'], $this->cartCurrencyIso);
            $productRow['tax'] = Tools::getAmountInCents($productRow['tax'], $this->cartCurrencyIso);
        }
    }
}
