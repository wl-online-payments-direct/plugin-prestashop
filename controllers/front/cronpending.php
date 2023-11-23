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
 * Class WorldlineopCronPendingModuleFrontController
 */
class WorldlineopCronPendingModuleFrontController extends ModuleFrontController
{
    /** @var Worldlineop */
    public $module;

    /** @var bool */
    private $verbose;

    /** @var int */
    private $idOrder;

    /**
     * WorldlineopCronPendingModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    /**
     * @param string $data
     */
    public function printDebug($data)
    {
        if ($this->verbose) {
            printf("$data<br>");
        }
    }

    /**
     * @param string $data
     */
    public function printOrderDebug($data)
    {
        $this->printDebug(sprintf('Order #%d - %s', $this->idOrder, $data));
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayAjaxRunCron()
    {
        $secureKey = \Tools::getValue('secure_key');
        if ($secureKey !== \WorldlineOP\PrestaShop\Utils\Tools::hash($this->module->getLocalPath())) {
            header('HTTP/1.1 200 OK');
            exit;
        }
        $this->verbose = Tools::getValue('verbose') ? true : false;
        if ($this->verbose) {
            printf('<pre>');
        }
        $shops = Shop::getShops(false, null, true);
        /** @var \WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('worldlineop.settings.loader');
        $shopSettings = [];
        $pendingStateIds = [];
        foreach ($shops as $idShop) {
            $settings = $settingsLoader->setContext($idShop);
            $shopSettings[$idShop] = $settings;
            $pendingStateIds[] = $settings->advancedSettings->paymentSettings->pendingOrderStateId;
        }

        $implode = implode(
            ',',
            array_map(
                function ($value) {
                    return (int) $value;
                },
                $pendingStateIds
            )
        );
        $dbQuery = new DbQuery();
        $dbQuery
            ->select('o.id_order')
            ->from('orders', 'o')
            ->where('o.module = "' . pSQL($this->module->name) . '"')
            ->where('o.current_state IN (' . pSQL($implode) . ')');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
        if (!$rows) {
            $this->printDebug('No orders eligible');
            exit;
        }
        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        /** @var \WorldlineOP\PrestaShop\Repository\TransactionRepository $transactionRepository */
        $rows = array_map(
            function ($array) {
                return $array['id_order'];
            },
            $rows
        );
        $this->printDebug('Orders IDs : ' . json_encode($rows) . '<br>');

        foreach ($rows as $idOrder) {
            $this->idOrder = (int) $idOrder;
            try {
                $order = new Order((int) $idOrder);
            } catch (Exception $e) {
                $this->printOrderDebug('Cannot load order');
                continue;
            }
            /** @var \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings */
            $settings = $shopSettings[$order->id_shop];
            if ($order->current_state != $settings->advancedSettings->paymentSettings->pendingOrderStateId) {
                $this->printOrderDebug('Shop does not match status');
                continue;
            }
            $date1 = $order->date_add;
            $date2 = date('Y-m-d H:i:s');
            $datetime1 = new DateTime($date1);
            $datetime2 = new DateTime($date2);
            $interval = $datetime1->diff($datetime2);
            if ($interval->format('%h') > $settings->advancedSettings->paymentSettings->retentionHours) {
                $this->printOrderDebug('Order is about to be cancelled');
                $orderHistory = new \OrderHistory();
                $orderHistory->id_order = (int) $idOrder;
                try {
                    $orderHistory->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), $idOrder);
                    $orderHistory->addWithemail();
                } catch (\Exception $e) {
                    $this->printOrderDebug('Order could not be cancelled');
                }
                continue;
            } else {
                $this->printOrderDebug('Order is not elligible yet.');
            }
        }

        if ($this->verbose) {
            $this->printDebug('End of process');
            printf('</pre>');
        }
        exit;
    }
}
