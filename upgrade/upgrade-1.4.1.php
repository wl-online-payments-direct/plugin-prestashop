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

/**
 * @param Worldlineop $module
 */
function upgrade_module_1_4_1($module)
{
    $logger = $module->logger->withName('Upgrade_1_4_1');
    $logger->info('Upgrade to v1.4.1 started');
    $module->unregisterHook('displayBackOfficeFooter');
    $logger->info('Hook deleted', ['hook' => 'displayBackOfficeFooter']);
    $logger->info('Upgrade to v1.4.1 finished with success');

    return true;
}
