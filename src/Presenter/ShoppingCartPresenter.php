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

if (!defined('_PS_VERSION_')) {
    exit;
}

use WorldlineOP\PrestaShop\Builder\HostedPaymentRequestBuilder;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class ShoppingCartPresenter
 */
class ShoppingCartPresenter implements PresenterInterface
{
    /** @var \Cart */
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
     * @param \Cart|false $cart
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
        $this->splitIntoUnitPricedRows($rows['products']);
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
            $this->discountShippingWithoutTax = $this->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING);
            $this->discountShippingWithTax = $this->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING);
            $this->discountProductsWithTax = $this->cart->getOrderTotal(true, \Cart::ONLY_DISCOUNTS) - $this->discountShippingWithTax;
        } else {
            $this->discountProductsWithTax = $this->cart->getOrderTotal(true, \Cart::ONLY_DISCOUNTS);
        }
        $productsWithTax = $this->cart->getOrderTotal(true, \Cart::ONLY_PRODUCTS);
        // A cart of nothing but free products totals zero; dividing by it aborts the payment request.
        $this->orderDiscountPercent = $productsWithTax > 0
            ? ((100 * $this->discountProductsWithTax) / $productsWithTax) / 100
            : 0;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getShippingRow()
    {
        $shippingWithTaxes = $this->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING);
        $shippingWithoutTaxes = $this->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING);

        return [
            'priceWithTax' => $this->discountShippingWithoutTax ? 0 : Tools::getRoundedAmountInCents($this->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING), $this->cartCurrencyIso),
            'priceWithoutTax' => Tools::getRoundedAmountInCents($this->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING), $this->cartCurrencyIso),
            'discountPrice' => Tools::getRoundedAmountInCents($this->discountShippingWithoutTax, $this->cartCurrencyIso),
            'priceDiscountedWithoutTax' => Tools::getRoundedAmountInCents($this->cart->getOrderTotal(false, \Cart::ONLY_SHIPPING) - $this->discountShippingWithoutTax, $this->cartCurrencyIso),
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
            $quantity = (int) $product['quantity'];
            if ($quantity < 1) {
                continue;
            }

            // The line total comes from PrestaShop itself: Cart::getProducts() fills 'total_wt'
            // honouring PS_ROUND_TYPE, so it always agrees with the cart total. Rebuilding it from
            // a rounded unit price instead makes every unit drift by up to half a minor unit, and
            // the accumulated drift is what used to push a line item below zero.
            $rows[] = [
                'totalWithTax' => Tools::getRoundedAmount($product['total_wt'], $this->cartCurrencyIso),
                'totalWithoutTax' => Tools::getRoundedAmount($product['total'], $this->cartCurrencyIso),
                'productPrice' => 0,
                'discountPrice' => 0,
                'tax' => 0,
                'quantity' => $quantity,
                'productCode' => $product['reference'] ?: $product['unique_id'],
                'productName' => $product['name'],
                'productId' => $product['id_product'],
                'productType' => !empty($this->productsType[$product['id_product']]) ? $this->productsType[$product['id_product']] : '',
                'data' => $product,
            ];
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

        return [
            [
                'totalWithTax' => $amounts['totalWithTax'],
                // Carrying the tax-exclusive total lets the shared unit-price pass recompute this
                // row like any other, so a rounding adjustment cannot desynchronise it.
                'totalWithoutTax' => $amounts['productPrice'],
                'productPrice' => $amounts['productPrice'],
                'discountPrice' => $amounts['discountPrice'],
                'tax' => $amounts['tax'],
                'quantity' => 1,
                'productName' => $productName,
                'productType' => $productType,
                'productCode' => 'Merged item',
            ]];
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
            // Line totals, not unit prices: the unit price of a line with quantity 20 counted once,
            // and the tax was accumulated from the running sums rather than from this product, so it
            // grew as a prefix-sum cascade and could exceed the order amount outright.
            $lineWithTax = Tools::getRoundedAmount($product['total_wt'], $this->cartCurrencyIso);
            $lineWithoutTax = Tools::getRoundedAmount($product['total'], $this->cartCurrencyIso);

            $totalWithTax += $lineWithTax;
            $productPrice += $lineWithoutTax;
            $tax += $lineWithTax - $lineWithoutTax;
        }

        return [
            'discountPrice' => 0,
            'productPrice' => $productPrice,
            'tax' => $tax,
            'totalWithTax' => $totalWithTax,
        ];
    }

    /**
     * Determines the merged product type based on priority:
     * - FoodAndDrink > HomeAndGarden > GiftAndFlowers
     *
     * @param array $products
     *
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
            ++$typeCounts[$type];
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
            // Merged rows (meal voucher flow) carry no source product; they are already cart-wide.
            if (!isset($productRow['data'])) {
                continue;
            }
            $rate = $productRow['data']['rate'] / 100;
            $unitPriceWithoutTax = $productRow['data']['price_with_reduction_without_tax'];
            $unitDiscountWithoutTax = $unitPriceWithoutTax * $this->orderDiscountPercent;
            $unitPriceDiscountedWithoutTax = $unitPriceWithoutTax - $unitDiscountWithoutTax;
            $unitTaxAmountDiscounted = $unitPriceDiscountedWithoutTax * $rate;

            $productRow['discountPrice'] = Tools::getRoundedAmount($unitDiscountWithoutTax, $this->cartCurrencyIso);
            // Multiply once at line level rather than summing a rounded per-unit figure.
            $productRow['totalWithTax'] = Tools::getRoundedAmount(
                ($unitPriceDiscountedWithoutTax + $unitTaxAmountDiscounted) * $productRow['quantity'],
                $this->cartCurrencyIso
            );
            $productRow['totalWithoutTax'] = Tools::getRoundedAmount(
                $unitPriceDiscountedWithoutTax * $productRow['quantity'],
                $this->cartCurrencyIso
            );
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
        if (empty($productRows)) {
            return;
        }
        $factor = 10 ** Tools::getCurrencyDecimalByIso($this->cartCurrencyIso);
        $totalCart = $this->cart->getOrderTotal() - $this->cart->getOrderTotal(true, \Cart::ONLY_SHIPPING) + $this->discountShippingWithTax;

        $lineTotals = [];
        $totalCalculated = 0;
        foreach ($productRows as $productRow) {
            $lineTotal = (int) round($productRow['totalWithTax'] * $factor);
            $lineTotals[] = $lineTotal;
            $totalCalculated += $lineTotal;
        }

        $remainder = $totalCalculated - (int) round($totalCart * $factor);
        if (0 === $remainder) {
            return;
        }

        $this->spreadRoundingRemainder($lineTotals, $remainder);

        foreach ($productRows as $index => &$productRow) {
            $productRow['totalWithTax'] = $lineTotals[$index] / $factor;
        }
    }

    /**
     * Rewrites the line-level rows into rows that carry an integer unit price.
     *
     * The API requires amountOfMoney.amount == (productPrice + taxAmount) * quantity, with every
     * value an integer in minor units. A line total is not always divisible by its quantity - 20
     * pieces for 0.74 EUR would need 3.7 cents each - so such a line is emitted as two rows: some
     * pieces one minor unit above the base price, the rest at the base price. That is the shape the
     * module has always sent; the defect was that the whole remainder was loaded onto a single
     * piece instead of being spread over as many pieces as it takes.
     *
     * @param array $productRows
     *
     * @return void
     *
     * @throws \Exception
     */
    private function splitIntoUnitPricedRows(&$productRows)
    {
        $factor = 10 ** Tools::getCurrencyDecimalByIso($this->cartCurrencyIso);
        $split = [];
        foreach ($productRows as $productRow) {
            foreach ($this->buildUnitPricedRows($productRow, $factor) as $unitPricedRow) {
                $split[] = $unitPricedRow;
            }
        }

        $productRows = $split;
    }

    /**
     * @param array $row
     * @param int $factor
     *
     * @return array
     */
    private function buildUnitPricedRows(array $row, $factor)
    {
        if (!isset($row['totalWithoutTax'])) {
            return [$row];
        }

        $lineWithTax = max(0, (int) round($row['totalWithTax'] * $factor));
        // The remainder spread moves the tax-inclusive total only, so it can end up below the
        // tax-exclusive one; clamping keeps the derived tax from turning negative, which the API
        // rejects. A zero-rated line legitimately has both totals equal.
        $lineWithoutTax = min(max(0, (int) round($row['totalWithoutTax'] * $factor)), $lineWithTax);
        $quantity = max(1, (int) $row['quantity']);

        // Integer division leaves a remainder of at most $quantity - 1 minor units, so the split
        // below always yields whole-minor-unit prices and preserves the real quantity, whatever the
        // line total is - including a free line, where every piece simply prices at zero.
        $baseUnit = intdiv($lineWithTax, $quantity);
        $higherCount = $lineWithTax - ($baseUnit * $quantity);

        $groups = [];
        if ($higherCount > 0) {
            $groups[] = [$baseUnit + 1, $higherCount];
        }
        if ($quantity - $higherCount > 0) {
            $groups[] = [$baseUnit, $quantity - $higherCount];
        }

        $rows = [];
        foreach ($groups as $group) {
            list($unitWithTax, $count) = $group;
            $unitWithoutTax = $lineWithTax > 0
                ? (int) round($unitWithTax * $lineWithoutTax / $lineWithTax)
                : 0;
            $rows[] = array_merge($row, [
                'totalWithTax' => ($unitWithTax * $count) / $factor,
                'productPrice' => $unitWithoutTax / $factor,
                'tax' => ($unitWithTax - $unitWithoutTax) / $factor,
                'quantity' => $count,
            ]);
        }

        return $rows;
    }

    /**
     * Spreads the rounding remainder across the lines instead of letting a single line absorb all of
     * it. Only the line amount is touched: productPrice is a per-unit field, and subtracting a
     * line-wide remainder from it is what produced negative unit prices. No line is taken below
     * zero, because the API rejects negative amounts.
     *
     * The remainder is handed out in equal shares rather than one minor unit at a time: on the
     * merged (meal voucher) path a single line can have to absorb a whole cart discount, which is
     * millions of minor units.
     *
     * @param int[] $lineTotals line totals in minor units, adjusted in place
     * @param int $remainder in minor units; positive means the lines overshoot the cart total
     *
     * @return void
     */
    private function spreadRoundingRemainder(&$lineTotals, $remainder)
    {
        $count = count($lineTotals);
        if ($remainder < 0) {
            $this->addRoundingRemainder($lineTotals, -$remainder, $count);

            return;
        }

        $pending = $remainder;
        // Each pass either clears the remainder or empties at least one line, so it cannot run more
        // times than there are lines.
        while ($pending > 0) {
            $share = max(1, intdiv($pending, $count));
            $taken = 0;
            for ($index = 0; $index < $count && $pending > 0; ++$index) {
                $take = min($share, $pending, $lineTotals[$index]);
                if ($take < 1) {
                    continue;
                }
                $lineTotals[$index] -= $take;
                $pending -= $take;
                $taken += $take;
            }
            if ($taken < 1) {
                return;
            }
        }
    }

    /**
     * @param int[] $lineTotals
     * @param int $pending
     * @param int $count
     *
     * @return void
     */
    private function addRoundingRemainder(&$lineTotals, $pending, $count)
    {
        // Nothing caps a line from above, so one pass is always enough.
        $share = intdiv($pending, $count);
        $extra = $pending % $count;
        for ($index = 0; $index < $count; ++$index) {
            $lineTotals[$index] += $share + ($index < $extra ? 1 : 0);
        }
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
