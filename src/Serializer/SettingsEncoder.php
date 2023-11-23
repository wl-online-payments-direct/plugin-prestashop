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

namespace WorldlineOP\PrestaShop\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Class SettingsEncoder
 */
class SettingsEncoder extends JsonEncoder
{
    /**
     * @param $data
     * @param $format
     * @param array $context
     *
     * @return bool|false|float|int|string
     */
    public function encode($data, $format, array $context = [])
    {
        unset($data['extra']);

        return parent::encode($data, $format, $context);
    }
}
