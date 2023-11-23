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

use Language;
use Monolog\Logger;
use Tab;

/**
 * Class TabManager
 */
class TabManager
{
    /** @var Logger */
    private $logger;

    /**
     * @param array $tabs
     * @param string $moduleName
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installTabs($tabs, $moduleName)
    {
        foreach ($tabs as $tab) {
            $this->logger->info(sprintf('Install tab %s', $tab['className']));
            $this->createTab($tab, $moduleName);
        }
    }

    /**
     * @param array $moduleTab
     * @param string $moduleName
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function createTab($moduleTab, $moduleName)
    {
        if (Tab::getIdFromClassName($moduleTab['className'])) {
            return;
        }
        $tab = new Tab();
        $tab->class_name = pSQL($moduleTab['className']);
        $tab->module = pSQL($moduleName);
        $tab->icon = '';
        $tab->id_parent = (int) Tab::getIdFromClassName($moduleTab['parentClassName']);
        $tab->active = true;
        $tab->name = [];
        $names = $moduleTab['names'];
        foreach (Language::getLanguages() as $lang) {
            $isoCode = $lang['iso_code'];
            $tabName = isset($names[$isoCode]) ? $names[$isoCode] : $names['en'];
            $tab->name[$lang['id_lang']] = pSQL($tabName);
        }

        if (!$tab->add()) {
            throw new \Exception('Cannot add menu.');
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
