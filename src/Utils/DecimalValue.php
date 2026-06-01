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

class DecimalValue
{
    /** @var string */
    private $value;

    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    public function getIntegerPart()
    {
        $dot = strpos($this->value, '.');

        return $dot === false ? $this->value : substr($this->value, 0, $dot);
    }

    public function __toString()
    {
        if (strpos($this->value, '.') === false) {
            return $this->value;
        }

        return rtrim(rtrim($this->value, '0'), '.') ?: '0';
    }
}
