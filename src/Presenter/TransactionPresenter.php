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
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class TransactionPresenter
 */
class TransactionPresenter implements PresenterInterface
{
    const STATUS_REFUND_REQUESTED = 'REFUND_REQUESTED';
    const STATUS_CAPTURE_REQUESTED = 'CAPTURE_REQUESTED';
    const STATUS_PAYMENT_CAPTURED = 'CAPTURED';
    const STATUS_PAYMENT_REFUNDED = 'REFUNDED';
    const STATUS_PAYMENT_REJECTED = 'REJECTED';

    /** @var Worldlineop */
    private $module;

    /** @var TransactionRepository */
    private $transactionRepository;

    /** @var MerchantClient */
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
     *
     * @return array
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function present($idOrder = false)
    {
        /** @var \WorldlineopTransaction $transaction */
        $transaction = $this->transactionRepository->findByIdOrder($idOrder);
        if (false === $transaction) {
            throw new \Exception('Cannot find Worldline transaction');
        }
        $transactionData = array();

        try {
            $paymentDetails = $this->merchantClient->payments()->getPaymentDetails($transaction->reference);
        } catch (\Exception $e) {
            throw new \Exception('Could not retrieve transaction details');
        }

        if ($paymentDetails) {
            foreach ($paymentDetails->getOperations() as $paymentDetail) {
                if (!in_array($paymentDetail->getStatus(), array(
                    self::STATUS_PAYMENT_REFUNDED,
                    self::STATUS_REFUND_REQUESTED,
                    self::STATUS_PAYMENT_REJECTED
                ))) {
                    try {
                        $payment = $this->merchantClient->payments()->getPayment($paymentDetail->getId());
                        $refunds = $this->merchantClient->payments()->getRefunds($paymentDetail->getId());
                        $captures = $this->merchantClient->payments()->getCaptures($paymentDetail->getId());
                        $paymentSpecificOutput = $this->getPaymentSpecificOutput(
                            $payment->getPaymentOutput()->getPaymentMethod(),
                            $payment->getPaymentOutput()
                        );
                    } catch (\Exception $e) {
                        throw new \Exception('Could not retrieve transaction details');
                    }

                    $currencyCode = $payment->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode();
                    $decimals = Tools::getCurrencyDecimalByIso($currencyCode);
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
                        $totalCaptured = $payment->getPaymentOutput()->getAcquiredAmount()->getAmount();
                    }
                    $capturableAmount = !$paymentDetails->getStatusOutput()->getIsAuthorized() ? 0 : Tools::getRoundedAmountFromCents($payment->getPaymentOutput()->getAmountOfMoney()->getAmount() - $totalCaptured + $totalPendingCapture, $currencyCode);
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
                    $refundableAmount = !$paymentDetails->getStatusOutput()->getIsRefundable() ? 0 : Tools::getRoundedAmountFromCents($totalCaptured - $totalRefunded + $totalPendingRefund, $currencyCode);
                    $apiErrors = $paymentDetails->getStatusOutput()->getErrors() ?: [];
                    $errors = [];
                    foreach ($apiErrors as $apiError) {
                        $errors[] = [
                            'id' => $apiError->getId(),
                            'code' => $apiError->getCode(),
                        ];
                    }

                    $liability = '';
                    $exemptionType = '';
                    $paymentOutput = $paymentDetails->getPaymentOutput();

                    $specificOutput = null;
                    if (null !== $paymentOutput) {
                        $specificOutput = $paymentOutput->getCardPaymentMethodSpecificOutput();
                    }

                    $threeDSecureResults = null;
                    if (null !== $specificOutput) {
                        $threeDSecureResults = $specificOutput->getThreeDSecureResults();
                    }

                    if (null !== $threeDSecureResults) {
                        $liability = $threeDSecureResults->getLiability();
                        $exemptionType = $threeDSecureResults->getAppliedExemption();
                    }

                    $order = new \Order((int)$idOrder);
                    $psOrderAmountMatch = true;
                    if ($order->total_paid_tax_incl) {
                        $worldlineAmount = (int)$payment->getPaymentOutput()->getAmountOfMoney()->getAmount();
                        $psAmount = (int)Tools::getRoundedAmountInCents($order->total_paid_tax_incl, $payment->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode());
                        $psOrderAmountMatch = ($worldlineAmount === $psAmount);
                    }

                    $surcharge = $payment->getPaymentOutput()->getSurchargeSpecificOutput();
                    $surchargeAmount = 0;
                    if (null !== $surcharge) {
                        $surchargeAmount = Tools::getRoundedAmountFromCents(
                            $payment->getPaymentOutput()->getSurchargeSpecificOutput()->getSurchargeAmount()->getAmount(),
                            $payment->getPaymentOutput()->getSurchargeSpecificOutput()->getSurchargeAmount()->getCurrencyCode()
                        );
                    }
                    $transactionData[] = [
                        'orderId' => $idOrder,
                        'payment' => [
                            'amount' => Tools::getRoundedAmountFromCents(
                                $paymentDetail->getAmountOfMoney()->getAmount(), $currencyCode),
                            'hasSurcharge' => !($surchargeAmount === 0),
                            'surchargeAmount' => $surchargeAmount,
                            'amountWithoutSurcharge' => Tools::getRoundedAmountFromCents(
                                $payment->getPaymentOutput()->getAmountOfMoney()->getAmount(), $currencyCode),
                            'psOrderAmountMatch' => $psOrderAmountMatch,
                            'currencyCode' => $currencyCode,
                            'reference' => $payment->getPaymentOutput()->getReferences()->getMerchantReference(),
                            'id' => $paymentDetail->getId(),
                            'status' => $paymentDetails->getStatus(),
                            'productId' => $paymentSpecificOutput->getPaymentProductId(),
                            'fraudResult' => !empty($paymentSpecificOutput->getFraudResults()) ?
                                $paymentSpecificOutput->getFraudResults()->getFraudServiceResult() : '',
                            'liability' => $liability,
                            'exemptionType' => $exemptionType,
                            'errors' => $errors,
                        ],
                        'actions' => [
                            'isAuthorized' => $paymentDetails->getStatusOutput()->getIsAuthorized(),
                            'isCancellable' => $paymentDetails->getStatusOutput()->getIsCancellable(),
                            'isRefundable' => $paymentDetails->getStatusOutput()->getIsRefundable(),
                        ],
                        'refunds' => [
                            'list' => $refundsData,
                            'refundableAmount' => number_format($refundableAmount, $decimals, '.', ''),
                            'totalPendingRefund' => Tools::getRoundedAmountFromCents($totalPendingRefund, $currencyCode),
                            'totalRefunded' => Tools::getRoundedAmountFromCents($totalRefunded, $currencyCode),
                        ],
                        'captures' => [
                            'list' => $capturesData,
                            'capturableAmount' => number_format($capturableAmount, $decimals, '.', ''),
                            'totalPendingCapture' => Tools::getRoundedAmountFromCents($totalPendingCapture, $currencyCode),
                            'totalCaptured' => Tools::getRoundedAmountFromCents($totalCaptured, $currencyCode),
                        ],
                    ];
                }
            }
        }

        return $transactionData;
    }

    /**
     * @param string $paymentMethod
     * @param PaymentOutput $paymentOutput
     *
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
     * @param string $paymentMethod
     * @param RefundOutput $refundOutput
     *
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
     * @param string $paymentMethod
     * @param CaptureOutput $captureOutput
     *
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
