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
if (!defined('_PS_VERSION_')) {
    exit;
}

use WorldlineOP\PrestaShop\Exception\ExceptionList;

/**
 * Class AdminWorldlineopAjaxController
 */
class AdminWorldlineopAjaxController extends ModuleAdminController
{
    /** @var Worldlineop */
    public $module;

    /** @var \Monolog\Logger */
    public $logger;

    /**
     * AdminWorldlineAjaxController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('Ajax');
    }

    public function ajaxProcessToggleAdvSettings()
    {
        Configuration::updateGlobalValue('WORLDLINEOP_SHOW_ADVANCED_SETTINGS', Tools::getValue('newState'));
        exit(json_encode(['errors' => false]));
    }

    /**
     * @throws PrestaShopException
     */
    public function ajaxProcessGetPaymentProducts()
    {
        $paymentType = Tools::getValue('type');
        /** @var \WorldlineOP\PrestaShop\Configuration\Product\GetProductsRequest $productRequest */
        $productRequest = $this->module->getService('worldlineop.settings.get_products');
        try {
            $paymentMethods = $productRequest->request($paymentType);
        } catch (Exception $e) {
            $this->ajaxDie(json_encode([
                'errors' => true,
                'message' => $e->getMessage(),
            ]));
        }

        $this->context->smarty->assign([
            'data' => [
                'paymentMethodsSettings' => [
                    $paymentType . 'PaymentMethods' => $paymentMethods,
                ],
            ],
            'type' => $paymentType,
            'name' => $paymentType . 'PaymentMethods',
        ]);

        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/worldlineop_configuration/_paymentMethodsList.tpl'
        );
        $this->ajaxDie(json_encode([
            'errors' => false,
            'html_result' => $html,
        ]));
    }

    public function ajaxProcessHideWhatsNew()
    {
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.advanced_settings.updater');
        try {
            $updater->update(['displayWhatsNew' => false]);
        } catch (ExceptionList $e) {
            exit(json_encode([
                'errors' => true,
                'messages' => $e->getExceptionsMessages(),
            ]));
        }

        exit(json_encode([
            'errors' => false,
        ]));
    }

    public function displayAjaxWhatsNew()
    {
        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/worldlineop_configuration/modal/_whatsnew.tpl'
        );

        exit(json_encode([
            'result_html' => $html,
            'errors' => [],
        ]));
    }

    public function ajaxProcessResetModal()
    {
        $this->context->smarty->assign([
            'loader' => $this->module->getPathUri() . '/views/img/icons/loader.svg',
        ]);
        $html = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/worldlineop_configuration/modal/_loading.tpl'
        );

        exit(json_encode([
            'result_html' => $html,
            'errors' => [],
        ]));
    }
}
