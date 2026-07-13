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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Decimal
{
    public static function multiply($a, $b)
    {
        return new DecimalValue(bcmul((string) $a, (string) $b, 10)); // @phpstan-ignore-line
    }

    public static function divide($a, $b)
    {
        if (bccomp((string) $b, '0', 10) === 0) { // @phpstan-ignore-line
            throw new \RuntimeException('Division by zero');
        }

        return new DecimalValue(bcdiv((string) $a, (string) $b, 10)); // @phpstan-ignore-line
    }
}
