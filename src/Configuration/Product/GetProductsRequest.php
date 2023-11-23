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

namespace WorldlineOP\PrestaShop\Configuration\Product;

use Configuration;
use Country;
use Currency;
use OnlinePayments\Sdk\Domain\GetPaymentProductsResponse;
use OnlinePayments\Sdk\Merchant\MerchantClient;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Logger\LoggerFactory;

/**
 * Class GetProductsRequest
 */
class GetProductsRequest
{
    /** @var MerchantClient */
    private $merchantClient;

    /** @var Settings */
    private $settings;

    /** @var \Monolog\Logger */
    private $logger;

    /**
     * GetProductRequest constructor.
     */
    public function __construct(MerchantClient $merchantClient, Settings $settings, LoggerFactory $loggerFactory)
    {
        $this->merchantClient = $merchantClient;
        $this->settings = $settings;
        $this->logger = $loggerFactory->setChannel('GetProductsRequest');
    }

    /**
     * @param string $paymentType
     *
     * @return array
     *
     * @throws \Exception
     */
    public function request($paymentType)
    {
        $query = new GetPaymentProductsParams();
        $defaultCurrency = Currency::getDefaultCurrency();
        $query->setCurrencyCode($defaultCurrency instanceof Currency ? $defaultCurrency->iso_code : 'EUR');
        $query->setCountryCode(Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')));
        if ('iframe' === $paymentType) {
            $query->setIsRecurring(true);
            $query->setHide(['productsWithRedirects ']);
        }
        /** @var GetPaymentProductsResponse $productsResponses */
        $productsResponses = $this->merchantClient->products()->getPaymentProducts($query);
        $this->logger->debug('GetPaymentProducts response', ['response' => json_decode($productsResponses->toJson(), true)]);

        /** @var \OnlinePayments\Sdk\Domain\PaymentProduct[] $products */
        $products = $productsResponses->getPaymentProducts();
        $paymentMethods = [];
        foreach ($products as $product) {
            if ('iframe' === $paymentType) {
                $existingProduct = $this->settings->paymentMethodsSettings->findIframePMByProductId($product->getId());
            } else {
                $existingProduct = $this->settings->paymentMethodsSettings->findRedirectPMByProductId($product->getId());
            }
            $enabled = $existingProduct ? $existingProduct->enabled : false;
            $paymentMethods[] = [
                'productId' => $product->getId(),
                'logo' => $product->getDisplayHints()->getLogo(),
                'type' => $product->getPaymentMethod(),
                'identifier' => $product->getDisplayHints()->getLabel(),
                'enabled' => $enabled,
            ];
        }

        return $paymentMethods;
    }
}
