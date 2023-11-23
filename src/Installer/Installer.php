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

namespace WorldlineOP\PrestaShop\Installer;

use Monolog\Logger;
use PrestaShopBundle\Install\SqlLoader;
use Symfony\Component\Yaml\Parser;
use WorldlineOP\PrestaShop\Configuration\Updater\AccountSettingsUpdater;
use WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater;
use WorldlineOP\PrestaShop\Configuration\Updater\PaymentMethodsSettingsUpdater;
use WorldlineOP\PrestaShop\Logger\LoggerFactory;
use WorldlineOP\PrestaShop\Utils\OrderStatusManager;
use WorldlineOP\PrestaShop\Utils\TabManager;

/**
 * Class Installer
 */
class Installer
{
    /** @var \Worldlineop */
    private $module;

    /** @var TabManager */
    private $tabManager;

    /** @var OrderStatusManager */
    private $orderStatusManager;

    /** @var AccountSettingsUpdater */
    private $accountSettingsUpdater;

    /** @var AdvancedSettingsUpdater */
    private $advancedSettingsUpdater;

    /** @var PaymentMethodsSettingsUpdater */
    private $paymentMethodsSettingsUpdater;

    /** @var string */
    private $psVersion;

    /** @var Logger */
    private $logger;

    /**
     * Installer constructor.
     *
     * @param \Worldlineop $module
     * @param TabManager $tabManager
     * @param OrderStatusManager $orderStatusManager
     * @param AccountSettingsUpdater $accountSettingsUpdater
     * @param AdvancedSettingsUpdater $advancedSettingsUpdater
     * @param PaymentMethodsSettingsUpdater $paymentMethodsSettingsUpdater
     * @param string $psVersion
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        \Worldlineop $module,
        TabManager $tabManager,
        OrderStatusManager $orderStatusManager,
        AccountSettingsUpdater $accountSettingsUpdater,
        AdvancedSettingsUpdater $advancedSettingsUpdater,
        PaymentMethodsSettingsUpdater $paymentMethodsSettingsUpdater,
        $psVersion,
        LoggerFactory $loggerFactory
    ) {
        $this->module = $module;
        $this->tabManager = $tabManager;
        $this->orderStatusManager = $orderStatusManager;
        $this->accountSettingsUpdater = $accountSettingsUpdater;
        $this->advancedSettingsUpdater = $advancedSettingsUpdater;
        $this->paymentMethodsSettingsUpdater = $paymentMethodsSettingsUpdater;
        $this->psVersion = $psVersion;
        $this->logger = $loggerFactory->setChannel('Install');
        $this->tabManager->setLogger($this->logger);
        $this->orderStatusManager->setLogger($this->logger);
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $context = ['module_version' => $this->module->version, 'prestashop_version' => $this->psVersion];
        $this->logger->info('Start install process', $context);
        $defaults = $this->getYaml();
        $this->checkTechnicalRequirements();
        $this->tabManager->installTabs($defaults['tabs'], $this->module->name);
        $this->registerHooks($defaults['hooks']);
        $this->installDb();
        $this->orderStatusManager->installOrderStatuses($defaults['orderStatuses'], $this->module->name);
        $this->logger->info('Applying account default configuration');
        $this->accountSettingsUpdater->update($defaults['configuration']['accountSettings']);
        $this->logger->info('Applying advanced default configuration');
        $defaults['configuration']['advancedSettings']['paymentSettings']['successOrderStateId'] = \Configuration::get('PS_OS_PAYMENT');
        $defaults['configuration']['advancedSettings']['paymentSettings']['pendingOrderStateId'] = \Configuration::getGlobalValue('WOP_PENDING_ORDER_STATUS_ID');
        $defaults['configuration']['advancedSettings']['paymentSettings']['errorOrderStateId'] = \Configuration::get('PS_OS_ERROR');
        $this->advancedSettingsUpdater->update($defaults['configuration']['advancedSettings']);
        \Configuration::updateGlobalValue('WORLDLINEOP_SHOW_ADVANCED_SETTINGS', false);
        $this->logger->info('Applying payment methods default configuration');
        $this->paymentMethodsSettingsUpdater->update($defaults['configuration']['paymentMethodsSettings']);
        $this->logger->info('Default configuration applied');
        $this->logger->info('Install process finished with success');
    }

    /**
     * @return mixed
     */
    public function getYaml()
    {
        $parser = new Parser();

        return $parser->parse(\Tools::file_get_contents($this->module->getLocalPath() . 'install/defaults.yml'));
    }

    /**
     * @throws \Exception
     */
    public function checkTechnicalRequirements()
    {
        //@formatter:off
        if (extension_loaded('curl') == false) {
            throw new \Exception(
                $this->module->l('You need to enable the cURL extension to use this module.', 'Installer')
            );
        }
        //@formatter:on
        $this->logger->info('Configuration meets technical requirements');
    }

    /**
     * @param array $hooks
     */
    public function registerHooks($hooks)
    {
        foreach ($hooks as $hook) {
            $this->logger->info(sprintf('Register hook %s', $hook));
            $this->module->registerHook($hook);
        }
    }

    public function installDb()
    {
        $sqlLoader = new SqlLoader();
        $sqlLoader->setMetaData([
            'PREFIX_' => _DB_PREFIX_,
        ]);
        $sqlLoader->parse_file($this->module->getLocalPath() . 'install/install.sql');
        $this->logger->info('Database updated');
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
