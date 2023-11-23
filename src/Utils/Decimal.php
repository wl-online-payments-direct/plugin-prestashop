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

namespace WorldlineOP\PrestaShop\Utils;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Number;

/**
 * Class Decimal
 */
class Decimal
{
    /**
     * @param $a
     * @param $b
     *
     * @return DecimalNumber
     */
    public static function multiply($a, $b)
    {
        $decimalA = new Number($a);
        $decimalB = new Number($b);

        return $decimalA->times($decimalB);
    }

    /**
     * @param string $a
     * @param string $b
     *
     * @return DecimalNumber
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function divide($a, $b)
    {
        $decimalA = new Number($a);
        $decimalB = new Number($b);

        return $decimalA->dividedBy($decimalB);
    }
}
