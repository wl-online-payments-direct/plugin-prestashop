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
use PrestaShopBundle\Install\SqlLoader;

/**
 * @param Worldlineop $module
 */
function upgrade_module_1_1_0($module)
{
    $logger = $module->logger->withName('Upgrade_1_1_0');
    $logger->info('Upgrade to v1.1.0 started');
    $sqlLoader = new SqlLoader();
    $sqlLoader->setMetaData([
        'PREFIX_' => _DB_PREFIX_,
    ]);
    try {
        $sqlLoader->parse_file($module->getLocalPath() . 'install/upgrade-1.1.0.sql', false);
    } catch (Exception $e) {
        $logger->error($e->getMessage());
    }
    $logger->info('Database updated');
    $hooksToRegister = ['displayPDFInvoice'];
    $module->registerHook($hooksToRegister);
    $logger->info('Hooks added', ['hooks' => $hooksToRegister]);
    $logger->info('Upgrade to v1.1.0 finished with success');

    return true;
}
