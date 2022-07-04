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

namespace WorldlineOP\PrestaShop\Utils;

use Currency;
use Customer;
use Language;
use Mail;
use Order;
use Symfony\Component\Filesystem\Filesystem;
use Validate;

/**
 * Class Tools
 * @package WorldlineOP\PrestaShop\Utils
 */
class Tools
{
    /**
     * @param string $value
     * @return string
     */
    public static function hash($value)
    {
        return md5(_COOKIE_IV_.$value);
    }

    /**
     * @param string $source
     * @param string $destination
     */
    public static function copy($source, $destination)
    {
        $filesystem = new Filesystem();
        $filesystem->copy($source, $destination, true);
    }

    /**
     * @return array
     */
    public static function getServerHttpHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (\Tools::substr($key, 0, 5) !== 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', \Tools::strtolower(\Tools::substr($key, 5)))));
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * @param int $idCurrency
     * @return string
     */
    public static function getIsoCurrencyCodeById($idCurrency)
    {
        $currency = new Currency((int) $idCurrency);
        if (!Validate::isLoadedObject($currency)) {
            return '';
        }

        return $currency->iso_code;
    }

    /**
     * @param int $idOrder
     * @return bool|int
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function sendPendingCaptureMail($idOrder)
    {
        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }
        $subjects = [
            'en' => 'Awaiting payment capture',
        ];
        $language = new Language((int) $order->id_lang);
        $customer = new Customer((int) $order->id_customer);

        return Mail::send(
            $order->id_lang,
            'pending_capture',
            isset($subjects[$language->iso_code]) ? $subjects[$language->iso_code] : $subjects['en'],
            [
                '{order_name}' => $order->reference,
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
            ],
            $customer->email,
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'worldlineop/mails/'
        );
    }

    /**
     * @param int $idCart
     * @return array|false
     * @throws \PrestaShopDatabaseException
     */
    public static function getOrderIdsByIdCart($idCart)
    {
        $dbQuery = new \DbQuery();
        $dbQuery
            ->select('id_order')
            ->from('orders')
            ->where('id_cart = '.(int) $idCart);

        $results = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
        if (!$results) {
            return false;
        }
        $orderIds = [];
        foreach ($results as $result) {
            $orderIds[] = (int) $result['id_order'];
        }

        return $orderIds;
    }
}
