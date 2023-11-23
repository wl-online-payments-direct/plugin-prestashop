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
 * @param Worldlineop $module
 *
 * @throws PrestaShopException
 */
function upgrade_module_1_3_1($module)
{
    $logger = $module->logger->withName('Upgrade_1_3_1');
    $logger->info('Upgrade to v1.3.1 started');
    /** @var \WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader $settingsLoader */
    $settingsLoader = $module->getService('worldlineop.settings.loader');
    $settingsArray = $settingsLoader->normalize();

    $paymentSettings = [
        'paymentSettings' => $settingsArray['advancedSettings']['paymentSettings'],
    ];
    $paymentSettings['paymentSettings']['safetyDelay'] = 12;
    $paymentMethodsSettings = [];
    Shop::setContext(Shop::CONTEXT_ALL);
    /** @var \WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater $advancedSettingsUpdater */
    $advancedSettingsUpdater = $module->getService('worldlineop.settings.advanced_settings.updater');
    if (Configuration::hasContext('WORLDLINEOP_ADVANCED_SETTINGS', null, Shop::getContext())) {
        try {
            update_advanced_settings_1_3_1($advancedSettingsUpdater, $paymentSettings);
        } catch (ExceptionList $e) {
            foreach ($e->getExceptionsMessages() as $exceptionsMessage) {
                $logger->error($exceptionsMessage);
            }
        }
    }
    $shops = Shop::getShops();
    foreach ($shops as $shop) {
        Shop::setContext(Shop::CONTEXT_SHOP, (int) $shop['id_shop']);
        $settingsLoader->setContext((int) $shop['id_shop']);
        $settingsArray = $settingsLoader->normalize();
        $paymentSettings = [
            'paymentSettings' => $settingsArray['advancedSettings']['paymentSettings'],
        ];
        $paymentSettings['paymentSettings']['safetyDelay'] = 12;
        if (Configuration::hasKey('WORLDLINEOP_ADVANCED_SETTINGS', null, null, (int) $shop['id_shop'])) {
            try {
                update_advanced_settings_1_3_1($advancedSettingsUpdater, $paymentSettings);
            } catch (ExceptionList $e) {
                foreach ($e->getExceptionsMessages() as $exceptionsMessage) {
                    $logger->error($exceptionsMessage);
                }
            }
        }
    }

    $logger->info('Upgrade to v1.3.1 finished with success');

    return true;
}

/**
 * @param \WorldlineOP\PrestaShop\Configuration\Updater\AdvancedSettingsUpdater $advancedSettingsUpdater
 * @param array $array
 *
 * @return void
 *
 * @throws ExceptionList
 */
function update_advanced_settings_1_3_1($advancedSettingsUpdater, $array)
{
    $advancedSettingsUpdater->update($array);
}
