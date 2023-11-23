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

use OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentSettings;
use WorldlineOP\PrestaShop\Utils\Decimal;

/**
 * Class WorldlineopCronCaptureModuleFrontController
 */
class WorldlineopCronCaptureModuleFrontController extends ModuleFrontController
{
    /** @var Worldlineop */
    public $module;

    /** @var bool */
    private $verbose;

    /** @var int */
    private $idOrder;

    /**
     * WorldlineopCronCaptureModuleFrontController constructor.
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
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
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
        /** @var \WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader $settingsLoader */
        $settingsLoader = $this->module->getService('worldlineop.settings.loader');
        $shopsSettings = [];
        $restrictOSIds1 = [Configuration::getGlobalValue('PS_OS_CANCELED')];
        $restrictOSIds2 = [Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID')];
        $implode1 = implode(
            ',',
            array_map(
                function ($value) {
                    return (int) $value;
                },
                $restrictOSIds1
            )
        );
        $subQuery1 = new DbQuery();
        $subQuery1
            ->select('oh.id_order')
            ->from('order_history', 'oh')
            ->where('oh.id_order_state IN(' . pSQL($implode1) . ')');
        $implode2 = implode(
            ',',
            array_map(
                function ($value) {
                    return (int) $value;
                },
                $restrictOSIds2
            )
        );
        $subQuery2 = new DbQuery();
        $subQuery2
            ->select('oh.id_order')
            ->from('order_history', 'oh')
            ->where('oh.id_order_state IN(' . pSQL($implode2) . ')');
        $dbQuery = new DbQuery();
        $dbQuery
            ->select('o.id_order')
            ->from('orders', 'o')
            ->leftJoin('worldlineop_transaction', 'wt', 'wt.id_order = o.id_order')
            ->where('o.module = "' . pSQL($this->module->name) . '"')
            ->where('wt.id_order IS NOT NULL')
            ->having('o.id_order NOT IN(' . $subQuery1->build() . ') AND o.id_order IN(' . $subQuery2->build() . ')');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
        if (!$rows) {
            $this->printDebug('No orders eligible');
            exit;
        }
        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        /** @var \WorldlineOP\PrestaShop\Repository\TransactionRepository $transactionRepository */
        $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
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
            if (!isset($shopsSettings[$order->id_shop])) {
                $shopsSettings[$order->id_shop] = $settingsLoader->setContext($order->id_shop);
            }
            /** @var \WorldlineOP\PrestaShop\Configuration\Entity\Settings $settings */
            $settings = $shopsSettings[$order->id_shop];
            if (PaymentSettings::TRANSACTION_TYPE_IMMEDIATE === $settings->advancedSettings->paymentSettings->transactionType
                || 0 === $settings->advancedSettings->paymentSettings->captureDelay
            ) {
                $this->printOrderDebug('Capture does not need or cannot be made');
                continue;
            }
            $date1 = $order->date_add;
            $date2 = date('Y-m-d H:i:s');
            $datetime1 = new DateTime($date1);
            $datetime2 = new DateTime($date2);
            $interval = $datetime1->diff($datetime2);
            if ($interval->format('%a') < $settings->advancedSettings->paymentSettings->captureDelay) {
                $this->printOrderDebug('Transaction will be captured later');
                continue;
            }
            if ($interval->format('%a') > 32) {
                $this->printOrderDebug('Transaction is older than 32 days');
                continue;
            }
            /** @var WorldlineopTransaction $transaction */
            $transaction = $transactionRepository->findByIdOrder((int) $idOrder);
            if (false === $transaction) {
                $this->printOrderDebug('Cannot find transaction');
                continue;
            }
            try {
                $paymentResponse = $merchantClient->payments()->getPaymentDetails($transaction->reference);
                $captures = $merchantClient->payments()->getCaptures($transaction->reference);
            } catch (Exception $e) {
                $this->printOrderDebug($e->getMessage());
                continue;
            }
            if (!empty($captures->getCaptures())) {
                $this->printOrderDebug('Capture already done.');
                continue;
            }
            if (!$paymentResponse->getPaymentOutput()->getAmountOfMoney()->getAmount()) {
                $this->printOrderDebug('Amount of 0. Transaction probably cancelled.');
                continue;
            }
            if (!$paymentResponse->getStatusOutput()->getIsAuthorized()) {
                $this->printOrderDebug('Capture is not offered for transaction ' . $paymentResponse->getId());
                continue;
            }
            $amount = $paymentResponse->getPaymentOutput()->getAmountOfMoney()->getAmount();
            $currency = $paymentResponse->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode();
            $pow = \WorldlineOP\PrestaShop\Utils\Tools::getCurrencyDecimalByIso($currency);
            $this->printOrderDebug(sprintf(
                'About to capture %s for transaction ID %s',
                Decimal::divide((string) $amount, (string) pow(10, $pow)) . $currency,
                $paymentResponse->getId()
            ));
            $capturePaymentRequest = new CapturePaymentRequest();
            $capturePaymentRequest->setAmount($amount);
            try {
                $captureResponse = $merchantClient->payments()
                    ->capturePayment($transaction->reference, $capturePaymentRequest);
            } catch (Exception $e) {
                $this->printOrderDebug($e->getMessage());
                continue;
            }
            $this->printOrderDebug('Capture done. Status ' . $captureResponse->getStatus());
        }
        if ($this->verbose) {
            $this->printDebug('End of process');
            printf('</pre>');
        }
        exit;
    }
}
