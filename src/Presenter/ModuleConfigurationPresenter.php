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

namespace WorldlineOP\PrestaShop\Presenter;

use OrderState;
use Worldlineop;
use WorldlineOP\PrestaShop\Configuration\Entity\AccountSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentSettings;
use WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class ModuleConfigurationPresenter
 */
class ModuleConfigurationPresenter implements PresenterInterface
{
    const PENDING_CRON = '*/30 * * * * wget -O /dev/null ';
    const CAPTURE_CRON = '0 */6 * * * wget -O /dev/null ';

    /** @var SettingsLoader */
    private $settingsLoader;

    /** @var Worldlineop */
    private $module;

    /**
     * ModuleConfigurationPresenter constructor.
     *
     * @param Worldlineop $module
     * @param SettingsLoader $settingsLoader
     */
    public function __construct(Worldlineop $module, SettingsLoader $settingsLoader)
    {
        $this->module = $module;
        $this->settingsLoader = $settingsLoader;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    public function present()
    {
        $settings = $this->settingsLoader->normalize();
        $settings['extra'] = [
            'moduleVersion' => $this->module->version,
            'advancedSettingsEnabled' => \Configuration::getGlobalValue('WORLDLINEOP_SHOW_ADVANCED_SETTINGS'),
            'path' => [
                'module' => sprintf(__PS_BASE_URI__ . 'modules/%s/', $this->module->name),
                'img' => sprintf(__PS_BASE_URI__ . 'modules/%s/views/img/', $this->module->name),
                'controllers' => [
                    'webhooks' => \Context::getContext()->link->getModuleLink($this->module->name, 'webhook', []),
                    'captureCron' => self::CAPTURE_CRON . \Context::getContext()->link->getModuleLink(
                        $this->module->name,
                        'croncapture',
                        ['secure_key' => Tools::hash($this->module->getLocalPath()), 'ajax' => 1, 'action' => 'runcron']
                    ),
                    'pendingCron' => self::PENDING_CRON . \Context::getContext()->link->getModuleLink(
                        $this->module->name,
                        'cronpending',
                        ['secure_key' => Tools::hash($this->module->getLocalPath()), 'ajax' => 1, 'action' => 'runcron']
                    ),
                ],
            ],
            'const' => [
                'ACCOUNT_MODE_TEST' => AccountSettings::ACCOUNT_MODE_TEST,
                'ACCOUNT_MODE_PROD' => AccountSettings::ACCOUNT_MODE_PROD,
                'TRANSACTION_TYPE_IMMEDIATE' => PaymentSettings::TRANSACTION_TYPE_IMMEDIATE,
                'TRANSACTION_TYPE_AUTH' => PaymentSettings::TRANSACTION_TYPE_AUTH,
                'CAPTURE_DELAY_MIN' => PaymentSettings::CAPTURE_DELAY_MIN,
                'CAPTURE_DELAY_MAX' => PaymentSettings::CAPTURE_DELAY_MAX,
                'RETENTION_DELAY_MIN' => PaymentSettings::RETENTION_DELAY_MIN,
                'RETENTION_DELAY_MAX' => PaymentSettings::RETENTION_DELAY_MAX,
                'SAFETY_DELAY_MIN' => PaymentSettings::SAFETY_DELAY_MIN,
                'SAFETY_DELAY_MAX' => PaymentSettings::SAFETY_DELAY_MAX,
            ],
            'statuses' => OrderState::getOrderStates(\Context::getContext()->employee->id_lang),
            'defaultStatuses' => \Configuration::getMultiple([
                'PS_OS_PAYMENT',
                'WOP_PENDING_ORDER_STATUS_ID',
                'PS_OS_ERROR',
            ]),
        ];

        return $settings;
    }
}
