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

use Alcohol\ISO4217;
use Order;
use Symfony\Component\Filesystem\Filesystem;
use WorldlineOP\PrestaShop\Builder\HostedPaymentRequestBuilder;

/**
 * Class Tools
 */
class Tools
{
    /**
     * Whether this checkout may save a card, and offer previously saved ones.
     *
     * Two conditions, deliberately answered in one place so the three call sites cannot drift:
     *
     *  - the merchant's "Enable saving cards" setting, read as `false !==` rather than cast to bool.
     *    The setting defaults to enabled and a shop that upgraded has no stored value at all, so an
     *    absent value must read as allowed, not as disallowed;
     *  - guest checkout, where a card is never saved whatever the setting says. PrestaShop still
     *    creates a customer row for a guest (`is_guest = 1`), so the customer id alone cannot tell
     *    the two apart.
     *
     * @param \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings
     * @param \Context $context
     *
     * @return bool
     */
    public static function isCardSavingAllowed($settings, \Context $context)
    {
        if (false === $settings->advancedSettings->enableSavingCards) {
            return false;
        }

        return !self::isGuestCheckout($context);
    }

    /**
     * @param \Context $context
     *
     * @return bool
     */
    public static function isGuestCheckout(\Context $context)
    {
        return isset($context->customer) && (bool) $context->customer->is_guest;
    }

    /**
     * The same decision for code that has a customer id but no usable Context.
     *
     * The hosted-checkout return and webhook routes persist a token from data carried in the
     * response, and may run outside the shopper's session - so the guest flag has to come from the
     * customer row named by that response, not from Context. A row that will not load is treated as
     * not allowed: this is a safeguard, and refusing to persist is the safe direction.
     *
     * @param \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings
     * @param int $idCustomer
     *
     * @return bool
     */
    public static function isCardSavingAllowedForCustomer($settings, $idCustomer)
    {
        if (false === $settings->advancedSettings->enableSavingCards) {
            return false;
        }
        $customer = new \Customer((int) $idCustomer);
        if (!\Validate::isLoadedObject($customer)) {
            return false;
        }

        return !(bool) $customer->is_guest;
    }

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
        $currency = new \Currency((int) $idCurrency);
        if (!\Validate::isLoadedObject($currency)) {
            return '';
        }

        return $currency->iso_code;
    }

    /**
     * @param string $isoCode
     *
     * @return \Currency|false
     */
    public static function getCurrencyByIsoCode($isoCode)
    {
        $dbQuery = new \DbQuery();
        $dbQuery
            ->select('id_currency')
            ->from('currency')
            ->where('iso_code = "' . pSQL($isoCode) . '"');

        $idCurrency = \Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($dbQuery);
        $currency = new \Currency((int) $idCurrency);

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
        if (false === $pow) { // @phpstan-ignore-line
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
        if (false === $pow) { // @phpstan-ignore-line
            return $amount;
        }

        return (string) Decimal::multiply((string) \Tools::ps_round($amount, $pow), (string) pow(10, $pow))->getIntegerPart();
    }

    /**
     * @param float $amount
     * @param string $isoCurrency
     *
     * @return float|string
     */
    public static function getRoundedAmountFromCents($amount, $isoCurrency)
    {
        $pow = self::getCurrencyDecimalByIso($isoCurrency);
        if (false === $pow) { // @phpstan-ignore-line
            return $amount;
        }

        return number_format((float) (string) Decimal::divide((string) $amount, (string) pow(10, $pow)), $pow, '.', '');
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
        if (false === $pow) { // @phpstan-ignore-line
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
        $order = new \Order((int) $idOrder);
        if (!\Validate::isLoadedObject($order)) {
            return false;
        }
        $subjects = [
            'en' => 'Awaiting payment capture',
        ];
        $language = new \Language((int) $order->id_lang);
        $customer = new \Customer((int) $order->id_customer);

        return \Mail::send(
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

        // Always get order ids from master and make sure that DB query cache is not affecting the query outcome
        $results = \Db::getInstance()->executeS($dbQuery, true, false);
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

    /**
     * @param string|null $env
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    public static function removeSymfonyCache($env = null)
    {
        if (null === $env) {
            $env = defined('_PS_ENV_') ? _PS_ENV_ : (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ ? 'dev' : 'prod');
        }
        $dir = _PS_ROOT_DIR_ . '/var/cache/' . $env . '/';
        register_shutdown_function(function () use ($dir) {
            $fs = new Filesystem();
            $fs->remove($dir);
            \Hook::exec('actionClearSf2Cache');
        });
    }
}
