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

use OnlinePayments\Sdk\Client;
use OnlinePayments\Sdk\Communicator;
use OnlinePayments\Sdk\CommunicatorConfiguration;
use OnlinePayments\Sdk\DefaultConnection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use WorldlineOP\PrestaShop\Configuration\Entity\AccountSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Exception\ExceptionList;

/**
 * Class AdminWorldlineopConfigurationController
 */
class AdminWorldlineopConfigurationController extends ModuleAdminController
{
    const TAB_ACCOUNT = 'account';
    const TAB_ADVANCED_SETTINGS = 'advancedSettings';
    const TAB_PAYMENT_METHODS = 'paymentMethods';

    /** @var Worldlineop */
    public $module;

    /** @var string */
    private $activeTab;

    /** @var array */
    private $postedData;

    /**
     * AdminWorldlineopConfigurationController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->activeTab = self::TAB_ACCOUNT;
    }

    /**
     * @param bool $isNewTheme
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->context->controller->addCSS([$this->module->getLocalPath() . '/views/css/config.css']);
        /** @var \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings */
        $settings = $this->module->getService('worldlineop.settings');
        // @formatter:off
        Media::addJsDef([
            'worldlineopAjaxToken' => Tools::getAdminTokenLite('AdminWorldlineopAjax'),
            'genericErrorMessage' => $this->module->l('An error occurred during the process, please try again', 'AdminWorldlineopConfigurationController'),
            'showWhatsNew' => $settings->advancedSettings->displayWhatsNew === true,
            'copyMessage' => $this->module->l('Copied!', 'AdminWorldlineopConfigurationController'),
        ]);
        // @formatter:on
    }

    public function setModals()
    {
        $this->context->smarty->assign([
            'loader' => $this->module->getPathUri() . '/views/img/icons/loader.svg',
        ]);
        // @formatter:off
        $this->modals[] = [
            'modal_id' => 'worldlineop-modal-whatsnew',
            'modal_class' => 'modal-lg',
            'modal_title' => $this->module->l('Latest version - What\'s new?', 'AdminWorldlineopConfigurationController'),
            'modal_content' => $this->createTemplate('modal/_loading.tpl')->fetch(),
        ];
        // @formatter:on
    }

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        $this->setModals();
        /** @var \WorldlineOP\PrestaShop\Presenter\ModuleConfigurationPresenter $presenter */
        $presenter = $this->module->getService('worldlineop.settings.presenter');
        $data = $presenter->present();
        $data['activeTab'] = $this->activeTab;
        if (!empty($this->postedData)) {
            $data = array_replace_recursive($data, $this->postedData);
        }
        $this->context->smarty->assign([
            'data' => $data,
            'languages' => $this->getLanguages(),
        ]);

        $this->content = $this->createTemplate('layout.tpl')->fetch();
        parent::initContent();
    }

    public function processSaveAccountForm()
    {
        $this->activeTab = self::TAB_ACCOUNT;
        if (Tools::isSubmit('submitTestCredentialsForm')) {
            if (false === $this->testCredentials()) {
                return;
            }
            $this->saveAccount();
            $this->updatePaymentMethods();
        }
        if (Tools::isSubmit('submitSaveAccountForm')) {
            if (false === $this->saveAccount()) {
                return;
            }
            $this->updatePaymentMethods();
        }
    }

    public function saveAccount()
    {
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AccountSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.account.updater');
        $form = Tools::getValue('worldlineopAccountSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $errors = [];
            foreach ($updater->getViolations() as $violation) {
                $propertyAccessor->setValue($errors, $violation->getPropertyPath(), '');
            }
            $this->postedData['accountSettings'] = array_diff_key($form, $errors);
            $this->errors += $e->getExceptionsMessages();

            return;
        }
        // @formatter:off
        $this->confirmations[] = $this->module->l('Account settings saved successfully.', 'AdminWorldlineopConfigurationController');
        // @formatter:on
    }

    /**
     * @return bool
     */
    public function testCredentials()
    {
        $form = Tools::getValue('worldlineopAccountSettings');
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AccountSettingsUpdater $accountUpdater */
        $accountUpdater = $this->module->getService('worldlineop.settings.account.updater');
        $form = $accountUpdater->forceResolve($form);
        $accountTested = new AccountSettings();
        $accountTested = $accountUpdater->forceDenormalize($form, $accountTested);
        /** @var \WorldlineOP\PrestaShop\Configuration\Entity\Settings $savedSettings */
        $savedSettings = $this->module->getService('worldlineop.settings');
        $settings = new Settings();
        $settings->accountSettings = $accountTested;
        $settings->advancedSettings = $savedSettings->advancedSettings;
        $settings = $settings->postLoading();
        $connection = new DefaultConnection();
        $communicatorConfiguration = new CommunicatorConfiguration(
            $settings->credentials->apiKey,
            $settings->credentials->apiSecret,
            $settings->credentials->endpoint,
            'PrestaShop'
        );
        $communicator = new Communicator($connection, $communicatorConfiguration);
        $merchantClient = new Client($communicator);

        try {
            $testResponse = $merchantClient->merchant($settings->credentials->pspid)->services()->testConnection();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            return false;
        }
        if ($testResponse->getResult() !== 'OK') {
            // @formatter:off
            $this->errors[] = $this->module->l('Please verify your credentials', 'AdminWorldlineopConfigurationController');
            // @formatter:on

            return false;
        } else {
            // @formatter:off
            $this->confirmations[] = $this->module->l('Account credentials are valid.', 'AdminWorldlineopConfigurationController');
            // @formatter:on

            return true;
        }
    }

    /**
     * @return void
     */
    public function updatePaymentMethods()
    {
        /** @var \WorldlineOP\PrestaShop\Configuration\Product\GetProductsRequest $getProductsService */
        $getProductsService = $this->module->getService('worldlineop.settings.get_products');
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\PaymentMethodsSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.payment_methods.updater');
        try {
            $iframeProducts = $getProductsService->request('iframe');
            $redirectProducts = $getProductsService->request('redirect');
            $updater->update([
                'redirectPaymentMethods' => $redirectProducts,
                'iframePaymentMethods' => $iframeProducts,
            ]);
        } catch (Exception $e) {
            return;
        }
    }

    public function processSaveAdvancedSettingsForm()
    {
        $this->activeTab = self::TAB_ADVANCED_SETTINGS;
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.advanced_settings.updater');
        $form = Tools::getValue('worldlineopAdvancedSettings');
        try {
            $updater->update($form);
        } catch (ExceptionList $e) {
            $this->errors += $e->getExceptionsMessages();

            return;
        }
        // @formatter:off
        $this->confirmations[] = $this->module->l('Advanced settings saved successfully', 'AdminWorldlineopConfigurationController');
        // @formatter:on
    }

    public function processSavePaymentMethodsSettingsForm()
    {
        $this->activeTab = self::TAB_PAYMENT_METHODS;
        /** @var \WorldlineOP\PrestaShop\Configuration\Updater\PaymentMethodsSettingsUpdater $updater */
        $updater = $this->module->getService('worldlineop.settings.payment_methods.updater');
        $form = Tools::getValue('worldlineopPaymentMethodsSettings');
        try {
            $updater->update($form);
            $updater->updateGenericLogo(isset($form['deleteGenericLogo']));
            $updater->updateIframeLogo(isset($form['deleteLogo']));
        } catch (ExceptionList $e) {
            $this->errors += $e->getExceptionsMessages();

            return;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        // @formatter:off
        $this->confirmations[] = $this->module->l('Payment methods settings saved successfully', 'AdminWorldlineopConfigurationController');
        // @formatter:on
    }
}
