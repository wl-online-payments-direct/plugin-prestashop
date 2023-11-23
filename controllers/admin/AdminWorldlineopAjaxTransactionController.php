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

use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\ResponseException;
use WorldlineOP\PrestaShop\Utils\Decimal;

/**
 * Class AdminWorldlineopAjaxTransactionController
 */
class AdminWorldlineopAjaxTransactionController extends ModuleAdminController
{
    /** @var Worldlineop */
    public $module;

    /**
     * @throws Exception
     */
    public function displayAjaxCapture()
    {
        $transaction = Tools::getValue('transaction');
        if (!$this->access('edit')) {
            // @formatter:off
            $this->context->smarty->assign([
                'worldlineopAjaxTransactionError' => $this->module->l('You do not have permission to capture funds.', 'AdminWorldlineopAjaxTransactionController'),
            ]);
            // @formatter:on
            exit(json_encode([
                'result_html' => $this->module->hookAdminOrderCommon((int) $transaction['idOrder']),
            ]));
        }

        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');

        $pow = \WorldlineOP\PrestaShop\Utils\Tools::getCurrencyDecimalByIso($transaction['currencyCode']);
        $capturePaymentRequest = new CapturePaymentRequest();
        $capturePaymentRequest->setAmount((int) Decimal::multiply((string) $transaction['amountToCapture'], (string) pow(10, $pow))->getIntegerPart());
        try {
            $captureResponse = $merchantClient->payments()->capturePayment($transaction['id'], $capturePaymentRequest);
        } catch (ResponseException $re) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $re->getMessage());
            $this->module->logger->error('Capture request', ['request' => json_decode($capturePaymentRequest->toJson(), true)]);
            $this->module->logger->error('Response exception', ['response' => json_decode($re->getResponse()->toJson(), true)]);
        } catch (Exception $e) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $e->getMessage());
            $this->module->logger->error($e->getMessage());
        }
        if (isset($captureResponse)) {
            if (in_array($captureResponse->getStatus(), ['CAPTURED', 'CAPTURE_REQUESTED'])) {
                $this->context->smarty->assign('captureConfirmation', true);
            } else {
                // @formatter:off
                $this->context->smarty->assign('worldlineopAjaxTransactionError', $this->module->l('Capture of funds failed with status ', 'AdminWorldlineopAjaxTransactionController') . $captureResponse->getStatus());
                // @formatter:on
            }
        }

        $html = $this->module->hookAdminOrderCommon((int) $transaction['idOrder']);

        exit(json_encode(['result_html' => $html, 'success' => true]));
    }

    /**
     * @throws Exception
     */
    public function displayAjaxRefund()
    {
        $transaction = Tools::getValue('transaction');
        if (!$this->access('edit')) {
            // @formatter:off
            $this->context->smarty->assign([
                'worldlineopAjaxTransactionError' => $this->module->l('You do not have permission to refund funds.', 'AdminWorldlineopAjaxTransactionController'),
            ]);
            // @formatter:on
            exit(json_encode([
                'result_html' => $this->module->hookAdminOrderCommon((int) $transaction['idOrder']),
            ]));
        }
        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');

        $refundRequest = new RefundRequest();
        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setAmount(\WorldlineOP\PrestaShop\Utils\Tools::getAmountInCents($transaction['amountToRefund'], $transaction['currencyCode']));
        $amountOfMoney->setCurrencyCode($transaction['currencyCode']);
        $refundRequest->setAmountOfMoney($amountOfMoney);
        try {
            $refundResponse = $merchantClient->payments()->refundPayment($transaction['id'], $refundRequest);
        } catch (ResponseException $re) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $re->getMessage());
            $this->module->logger->error('Refund request', ['request' => json_decode($refundRequest->toJson(), true)]);
            $this->module->logger->error('Response exception', ['response' => json_decode($re->getResponse()->toJson(), true)]);
        } catch (Exception $e) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $e->getMessage());
            $this->module->logger->error($e->getMessage());
        }
        if (isset($refundResponse)) {
            if (in_array($refundResponse->getStatus(), ['REFUNDED', 'REFUND_REQUESTED'])) {
                $this->context->smarty->assign('refundConfirmation', true);
            } else {
                // @formatter:off
                $this->context->smarty->assign('worldlineopAjaxTransactionError', $this->module->l('Refund of funds failed with status ', 'AdminWorldlineopAjaxTransactionController') . $refundResponse->getStatus());
                // @formatter:on
            }
        }

        $html = $this->module->hookAdminOrderCommon((int) $transaction['idOrder']);

        exit(json_encode(['result_html' => $html, 'success' => true]));
    }

    /**
     * @throws Exception
     */
    public function displayAjaxCancel()
    {
        $transaction = Tools::getValue('transaction');
        if (!$this->access('edit')) {
            // @formatter:off
            $this->context->smarty->assign([
                'worldlineopAjaxTransactionError' => $this->module->l('You do not have permission to cancel transactions.', 'AdminWorldlineopAjaxTransactionController'),
            ]);
            // @formatter:on
            exit(json_encode([
                'result_html' => $this->module->hookAdminOrderCommon((int) $transaction['idOrder']),
            ]));
        }

        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');

        try {
            $cancelResponse = $merchantClient->payments()->cancelPayment($transaction['id']);
        } catch (ResponseException $re) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $re->getMessage());
            $this->module->logger->error('Cancel response exception', ['response' => json_decode($re->getResponse()->toJson(), true)]);
        } catch (Exception $e) {
            $this->context->smarty->assign('worldlineopAjaxTransactionError', $e->getMessage());
            $this->module->logger->error($e->getMessage());
        }
        if (isset($cancelResponse)) {
            $this->module->logger->debug('Cancel response', ['json' => json_decode($cancelResponse->toJson(), true)]);
            $this->context->smarty->assign('cancelConfirmation', true);
        }

        $html = $this->module->hookAdminOrderCommon((int) $transaction['idOrder']);

        exit(json_encode(['result_html' => $html, 'success' => true]));
    }
}
