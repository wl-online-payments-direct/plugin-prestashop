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

use Alcohol\ISO4217;
use Currency;
use Customer;
use Language;
use Mail;
use Order;
use Symfony\Component\Filesystem\Filesystem;
use Validate;
use WorldlineOP\PrestaShop\Builder\HostedPaymentRequestBuilder;

/**
 * Class Tools
 */
class Tools
{
    /**
     * @param string $value
     *
     * @return string
     */
    public static function hash($value)
    {
        return md5(_COOKIE_IV_ . $value);
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
     *
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
     * @param string $isoCode
     *
     * @return Currency|false
     */
    public static function getCurrencyByIsoCode($isoCode)
    {
        $dbQuery = new \DbQuery();
        $dbQuery
            ->select('id_currency')
            ->from('currency')
            ->where('iso_code = "' . pSQL($isoCode) . '"');

        $idCurrency = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);
        $currency = new Currency((int) $idCurrency);

        return \Validate::isLoadedObject($currency) ? $currency : false;
    }

    /**
     * @param string $iso
     *
     * @return int
     */
    public static function getCurrencyDecimalByIso($iso)
    {
        static $decimalCurrencies = [];

        if (isset($decimalCurrencies[$iso])) {
            return (int) $decimalCurrencies[$iso]['exp'];
        }

        $iso4217 = new ISO4217();
        try {
            $decimalCurrency = $iso4217->getByAlpha3($iso);
        } catch (\Exception $e) {
            return 2;
        }

        $decimalCurrencies[$iso] = $decimalCurrency;

        return (int) $decimalCurrencies[$iso]['exp'];
    }

    /**
     * @param float $amount
     * @param string $isoCurrency
     *
     * @return float|string
     */
    public static function getAmountInCents($amount, $isoCurrency)
    {
        $pow = self::getCurrencyDecimalByIso($isoCurrency);
        if (false === $pow) {
            return $amount;
        }

        return (string) Decimal::multiply((string) $amount, (string) pow(10, $pow))->getIntegerPart();
    }

    /**
     * @param float $amount
     * @param string $isoCurrency
     *
     * @return float|string
     */
    public static function getRoundedAmountInCents($amount, $isoCurrency)
    {
        $pow = self::getCurrencyDecimalByIso($isoCurrency);
        if (false === $pow) {
            return $amount;
        }

        return (string) Decimal::multiply((string) \Tools::ps_round($amount, $pow), (string) pow(10, $pow))->getIntegerPart();
    }

    /**
     * @param float $amount
     * @param string $isoCurrency
     *
     * @return float|string
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function getRoundedAmountFromCents($amount, $isoCurrency)
    {
        $pow = self::getCurrencyDecimalByIso($isoCurrency);
        if (false === $pow) {
            return $amount;
        }

        return number_format((string) Decimal::divide((string) $amount, (string) pow(10, $pow)), $pow, '.', '');
    }

    /**
     * @param float $amount
     * @param string $isoCurrency
     *
     * @return float|mixed
     */
    public static function getRoundedAmount($amount, $isoCurrency)
    {
        $pow = self::getCurrencyDecimalByIso($isoCurrency);
        if (false === $pow) {
            return $amount;
        }

        return \Tools::ps_round($amount, $pow);
    }

    /**
     * @param int $idOrder
     *
     * @return bool|int
     *
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
            _PS_MODULE_DIR_ . 'worldlineop/mails/'
        );
    }

    /**
     * @param int $idCart
     *
     * @return array|false
     *
     * @throws \PrestaShopDatabaseException
     */
    public static function getOrderIdsByIdCart($idCart)
    {
        if (!$idCart) {
            return [];
        }
        $dbQuery = new \DbQuery();
        $dbQuery
            ->select('id_order')
            ->from('orders')
            ->where('id_cart = ' . (int) $idCart);

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

    /**
     * @param float $amount
     * @param \Currency $currencyFrom
     *
     * @return float
     *
     * @throws \Exception
     */
    public static function getAmountInEuros($amount, $currencyFrom)
    {
        $currencyEUR = self::getCurrencyByIsoCode('EUR');
        if (false === $currencyEUR) {
            throw new \Exception('EURO currency is missing');
        }
        $amountInDefaultCurrency = Decimal::divide((string) $amount, (string) $currencyFrom->conversion_rate);

        return Decimal::multiply((string) $amountInDefaultCurrency, (string) $currencyEUR->conversion_rate)->__toString();
    }

    /**
     * @param int $idProduct
     *
     * @return false|string
     */
    public static function getGiftCardTypeByIdProduct($idProduct)
    {
        $dbQuery = new \DbQuery();
        $dbQuery
            ->select('product_type')
            ->from('worldlineop_product_gift_card')
            ->where('id_product = ' . (int) $idProduct);

        return \Db::getInstance()->getValue($dbQuery) ?: HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_NONE;
    }
}
