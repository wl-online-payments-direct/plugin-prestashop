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

use Configuration;
use Language;
use Monolog\Logger;
use OrderState;
use Validate;

/**
 * Class OrderStatusManager
 */
class OrderStatusManager
{
    /** @var Logger */
    private $logger;

    /**
     * @param array $orderStatuses
     * @param string $moduleName
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installOrderStatuses($orderStatuses, $moduleName)
    {
        foreach ($orderStatuses as $orderStatus) {
            $this->createOrderStatus($orderStatus, $moduleName);
        }
    }

    /**
     * @param array $orderStatus
     * @param string $moduleName
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createOrderStatus($orderStatus, $moduleName)
    {
        $orderState = new OrderState(Configuration::getGlobalValue($orderStatus['configKey']));
        if (!Validate::isLoadedObject($orderState) || $orderState->deleted) {
            $this->logger->info(sprintf('Install order status %s', $orderStatus['configKey']));
            $orderState->hydrate($orderStatus);
            $orderState->name = [];
            $orderState->module_name = pSQL($moduleName);
            $languages = Language::getLanguages(false);
            $names = $orderStatus['names'];
            foreach ($languages as $language) {
                $name = isset($names[$language['iso_code']]) ? $names[$language['iso_code']] : $names['en'];
                $orderState->name[(int) $language['id_lang']] = pSQL($name);
            }
            if ($orderState->save()) {
                if ($orderStatus['logo']) {
                    $source = realpath(_PS_MODULE_DIR_ . $moduleName . '/views/img/icons/' . $orderStatus['logo']);
                    $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $orderState->id . '.gif';
                    Tools::copy($source, $destination);
                }
                Configuration::updateGlobalValue($orderStatus['configKey'], (int) $orderState->id);
            }
        } else {
            $this->logger->info(sprintf('Order status %s already exists', $orderStatus['configKey']));
        }
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
