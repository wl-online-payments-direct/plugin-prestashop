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
 *
 */

namespace WorldlineOP\PrestaShop\Presenter;

use OnlinePayments\Sdk\Domain\CaptureOutput;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\PaymentOutput;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RefundCardMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RefundEWalletMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RefundMobileMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RefundOutput;
use OnlinePayments\Sdk\Domain\RefundRedirectMethodSpecificOutput;
use OnlinePayments\Sdk\Merchant\MerchantClient;
use Worldlineop;
use WorldlineOP\PrestaShop\Repository\TransactionRepository;

/**
 * Class TransactionPresenter
 * @package WorldlineOP\PrestaShop\Presenter
 */
class TransactionPresenter implements PresenterInterface
{
    const STATUS_REFUND_REQUESTED = 'REFUND_REQUESTED';
    const STATUS_CAPTURE_REQUESTED = 'CAPTURE_REQUESTED';
    const STATUS_PAYMENT_CAPTURED = 'CAPTURED';

    /** @var Worldlineop $module */
    private $module;

    /** @var TransactionRepository $transactionRepository */
    private $transactionRepository;

    /** @var MerchantClient $merchantClient */
    private $merchantClient;

    public function __construct(
        Worldlineop $module,
        TransactionRepository $transactionRepository,
        MerchantClient $merchantClient
    ) {
        $this->module = $module;
        $this->transactionRepository = $transactionRepository;
        $this->merchantClient = $merchantClient;
    }

    /**
     * @param false|int $idOrder
     * @return array
     * @throws \PrestaShopException
     */
    public function present($idOrder = false)
    {
        /** @var \WorldlineopTransaction $transaction */
        $transaction = $this->transactionRepository->findByIdOrder($idOrder);
        if (false === $transaction) {
            throw new \Exception('Cannot find Worldline transaction');
        }
        try {
            $paymentDetails = $this->merchantClient->payments()->getPaymentDetails($transaction->reference);
            $payment = $this->merchantClient->payments()->getPayment($transaction->reference);
            $refunds = $this->merchantClient->payments()->getRefunds($transaction->reference);
            $captures = $this->merchantClient->payments()->getCaptures($transaction->reference);
            $paymentSpecificOutput = $this->getPaymentSpecificOutput(
                $payment->getPaymentOutput()->getPaymentMethod(),
                $payment->getPaymentOutput()
            );
        } catch (\Exception $e) {
            throw new \Exception('Could not retrieve transaction details');
        }

        $capturesData = [];
        $totalCaptured = 0;
        $totalPendingCapture = 0;
        if (!empty($captures->getCaptures())) {
            foreach ($captures->getCaptures() as $capture) {
                if (self::STATUS_CAPTURE_REQUESTED === $capture->getStatus()) {
                    $totalPendingCapture += $capture->getCaptureOutput()->getAmountOfMoney()->getAmount();
                }
                if (self::STATUS_PAYMENT_CAPTURED === $capture->getStatus()) {
                    $totalCaptured += $capture->getCaptureOutput()->getAmountOfMoney()->getAmount();
                }
                $capturesData[] = [
                    'amount' => $capture->getCaptureOutput()->getAmountOfMoney()->getAmount(),
                    'currencyCode' => $capture->getCaptureOutput()->getAmountOfMoney()->getCurrencyCode(),
                ];
            }
        } elseif (empty($captures->getCaptures()) && !$payment->getStatusOutput()->getIsAuthorized() && self::STATUS_PAYMENT_CAPTURED === $payment->getStatus()) {
            $totalCaptured = $payment->getPaymentOutput()->getAmountOfMoney()->getAmount();
        }
        $capturableAmount = !$paymentDetails->getStatusOutput()->getIsAuthorized() ? 0 : ($payment->getPaymentOutput()->getAmountOfMoney()->getAmount() - ($totalCaptured + $totalPendingCapture)) / 100;
        if ($capturableAmount < 0) {
            $capturableAmount = 0;
        }

        $refundsData = [];
        $totalRefunded = 0;
        $totalPendingRefund = 0;
        if (!empty($refunds->getRefunds())) {
            foreach ($refunds->getRefunds() as $refund) {
                $refundSpecificOuput = $this->getRefundSpecificOutput(
                    $refund->getRefundOutput()->getPaymentMethod(),
                    $refund->getRefundOutput()
                );
                if (null !== $refundSpecificOuput) {
                    $totalRefunded += $refundSpecificOuput->getTotalAmountRefunded();
                }
                if (self::STATUS_REFUND_REQUESTED === $refund->getStatus()) {
                    $totalPendingRefund += $refund->getRefundOutput()->getAmountOfMoney()->getAmount();
                }
                $refundsData[] = [
                    'amount' => $refund->getRefundOutput()->getAmountOfMoney()->getAmount(),
                    'currencyCode' => $refund->getRefundOutput()->getAmountOfMoney()->getCurrencyCode(),
                    'id' => $refund->getId(),
                    'status' => $refund->getStatus(),
                ];
            }
        }
        $refundableAmount = !$paymentDetails->getStatusOutput()->getIsRefundable() ? 0 : ($totalCaptured - ($totalRefunded + $totalPendingRefund)) / 100;
        $apiErrors = $paymentDetails->getStatusOutput()->getErrors() ?: [];
        $errors = [];
        foreach ($apiErrors as $apiError) {
            $errors[] = [
                'id' => $apiError->getId(),
                'code' => $apiError->getCode(),
            ];
        }
        $liability = null !== $paymentDetails->getPaymentOutput()->getCardPaymentMethodSpecificOutput() ? $paymentDetails->getPaymentOutput()->getCardPaymentMethodSpecificOutput()->getThreeDSecureResults()->getLiability() : '';

        return [
            'orderId' => $idOrder,
            'payment' => [
                'amount' => $payment->getPaymentOutput()->getAmountOfMoney()->getAmount() / 100,
                'currencyCode' => $payment->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode(),
                'reference' => $payment->getPaymentOutput()->getReferences()->getMerchantReference(),
                'id' => $transaction->reference,
                'status' => $paymentDetails->getStatus(),
                'productId' => $paymentSpecificOutput->getPaymentProductId(),
                'fraudResult' => $paymentSpecificOutput->getFraudResults()->getFraudServiceResult(),
                'liability' => $liability,
                'errors' => $errors,
            ],
            'actions' => [
                'isAuthorized' => $paymentDetails->getStatusOutput()->getIsAuthorized(),
                'isCancellable' => $paymentDetails->getStatusOutput()->getIsCancellable(),
                'isRefundable' => $paymentDetails->getStatusOutput()->getIsRefundable(),
            ],
            'refunds' => [
                'list' => $refundsData,
                'refundableAmount' => $refundableAmount,
                'totalPendingRefund' => $totalPendingRefund / 100,
                'totalRefunded' => $totalRefunded / 100,
            ],
            'captures' => [
                'list' => $capturesData,
                'capturableAmount' => $capturableAmount,
                'totalPendingCapture' => $totalPendingCapture / 100,
                'totalCaptured' => $totalCaptured / 100,
            ],
        ];
    }

    /**
     * @param string        $paymentMethod
     * @param PaymentOutput $paymentOutput
     * @return CardPaymentMethodSpecificOutput|MobilePaymentMethodSpecificOutput|RedirectPaymentMethodSpecificOutput
     */
    public function getPaymentSpecificOutput($paymentMethod, PaymentOutput $paymentOutput)
    {
        switch ($paymentMethod) {
            case 'card':
            default:
                return $paymentOutput->getCardPaymentMethodSpecificOutput();
            case 'redirect':
                return $paymentOutput->getRedirectPaymentMethodSpecificOutput();
            case 'mobile':
                return $paymentOutput->getMobilePaymentMethodSpecificOutput();
        }
    }

    /**
     * @param string       $paymentMethod
     * @param RefundOutput $refundOutput
     * @return RefundCardMethodSpecificOutput|RefundEWalletMethodSpecificOutput|RefundMobileMethodSpecificOutput|RefundRedirectMethodSpecificOutput
     */
    public function getRefundSpecificOutput($paymentMethod, RefundOutput $refundOutput)
    {
        switch ($paymentMethod) {
            case 'card':
            default:
                return $refundOutput->getCardRefundMethodSpecificOutput();
            case 'wallet':
                return $refundOutput->getEWalletRefundMethodSpecificOutput();
            case 'redirect':
                return $refundOutput->getRedirectRefundMethodSpecificOutput();
            case 'mobile':
                return $refundOutput->getMobileRefundMethodSpecificOutput();
        }
    }

    /**
     * @param string        $paymentMethod
     * @param CaptureOutput $captureOutput
     * @return CardPaymentMethodSpecificOutput|MobilePaymentMethodSpecificOutput|RedirectPaymentMethodSpecificOutput
     */
    public function getCaptureSpecificOutput($paymentMethod, CaptureOutput $captureOutput)
    {
        switch ($paymentMethod) {
            case 'card':
            default:
                return $captureOutput->getCardPaymentMethodSpecificOutput();
            case 'redirect':
                return $captureOutput->getRedirectPaymentMethodSpecificOutput();
            case 'mobile':
                return $captureOutput->getMobilePaymentMethodSpecificOutput();
        }
    }
}
