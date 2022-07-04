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

use Ingenico\Direct\Sdk\Domain\GetPaymentProductsResponse;
use Ingenico\Direct\Sdk\Merchant\Products\GetPaymentProductsParams;
use WorldlineOP\PrestaShop\Exception\ExceptionList;

/**
 * Class AdminWorldlineopAjaxController
 */
class AdminWorldlineopAjaxController extends ModuleAdminController
{
    /** @var Worldlineop $module */
    public $module;

    /** @var \Monolog\Logger $logger */
    public $logger;

    /**
     * AdminWorldlineAjaxController constructor.
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('Ajax');
    }

    /**
     *
     */
    public function ajaxProcessToggleAdvSettings()
    {
        Configuration::updateGlobalValue('WORLDLINEOP_SHOW_ADVANCED_SETTINGS', Tools::getValue('newState'));
        die(json_encode(['errors' => false]));
    }

    /**
     * @throws PrestaShopException
     */
    public function ajaxProcessGetPaymentProducts()
    {
        $paymentType = Tools::getValue('type');
        /** @var \Ingenico\Direct\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        $query = new GetPaymentProductsParams();
        $defaultCurrency = Currency::getDefaultCurrency();
        $query->setCurrencyCode($defaultCurrency instanceof Currency ? $defaultCurrency->iso_code : 'EUR');
        $query->setCountryCode(Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')));
        if ('iframe' === $paymentType) {
            $query->setIsRecurring(true);
            $query->setHide(['productsWithRedirects ']);
        }
        try {
            /** @var GetPaymentProductsResponse $productsResponses */
            $productsResponses = $merchantClient->products()->getPaymentProducts($query);
            $this->logger->debug('GetPaymentProducts response', ['response' => json_decode($productsResponses->toJson(), true)]);
        } catch (Exception $e) {
            $this->ajaxDie(json_encode([
                'errors' => true,
                'message' => $e->getMessage(),
            ]));
        }

        /** @var \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings */
        $settings = $this->module->getService('worldlineop.settings');

        /** @var \Ingenico\Direct\Sdk\Domain\PaymentProduct[] $products */
        $products = $productsResponses->getPaymentProducts();
        $paymentMethods = [];
        foreach ($products as $product) {
            if ('iframe' === $paymentType) {
                $existingProduct = $settings->paymentMethodsSettings->findIframePMByProductId($product->getId());
            } else {
                $existingProduct = $settings->paymentMethodsSettings->findRedirectPMByProductId($product->getId());
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

        $this->context->smarty->assign([
            'data' => [
                'paymentMethodsSettings' => [
                    $paymentType.'PaymentMethods' => $paymentMethods,
                ],
            ],
            'type' => $paymentType,
            'name' => $paymentType.'PaymentMethods',
        ]);

        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/worldlineop_configuration/_paymentMethodsList.tpl'
        );
        $this->ajaxDie(json_encode([
            'errors' => false,
            'html_result' => $html,
        ]));
    }

    /**
     *
     */
    public function ajaxProcessHideWhatsNew()
    {
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.advanced_settings.updater');
        try {
            $updater->update(['displayWhatsNew' => false]);
        } catch (ExceptionList $e) {
            die(json_encode([
                'errors' => true,
                'messages' => $e->getExceptionsMessages(),
            ]));
        }

        die(json_encode([
            'errors' => false,
        ]));
    }

    /**
     *
     */
    public function displayAjaxWhatsNew()
    {
        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/worldlineop_configuration/modal/_whatsnew.tpl'
        );

        die(json_encode([
            'result_html' => $html,
            'errors' => [],
        ]));
    }

    /**
     *
     */
    public function ajaxProcessResetModal()
    {
        $this->context->smarty->assign([
            'loader' => $this->module->getPathUri().'/views/img/icons/loader.svg',
        ]);
        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/worldlineop_configuration/modal/_loading.tpl'
        );

        die(json_encode([
            'result_html' => $html,
            'errors' => [],
        ]));
    }
}
