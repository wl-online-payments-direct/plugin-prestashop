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

use Address;
use Cart;
use Context;
use Country;
use CreatedPayment;
use Currency;
use DateTime;
use HostedCheckout;
use Ingenico\Direct\Sdk\Domain\AmountOfMoney;
use Ingenico\Direct\Sdk\Domain\CardPaymentMethodSpecificOutput;
use Ingenico\Direct\Sdk\Domain\MobilePaymentMethodSpecificOutput;
use Ingenico\Direct\Sdk\Domain\PaymentOutput;
use Ingenico\Direct\Sdk\Domain\PaymentResponse;
use Ingenico\Direct\Sdk\Domain\RedirectPaymentMethodSpecificOutput;
use Ingenico\Direct\Sdk\Domain\RefundRequest;
use Ingenico\Direct\Sdk\Domain\RefundResponse;
use Ingenico\Direct\Sdk\Domain\WebhooksEvent;
use Ingenico\Direct\Sdk\Merchant\Products\GetPaymentProductParams;
use Order;
use Validate;
use Worldlineop;
use WorldlineOP\PrestaShop\Builder\PaymentRequestBuilder;
use WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader;
use WorldlineOP\PrestaShop\Logger\LoggerFactory;
use WorldlineOP\PrestaShop\Repository\CreatedPaymentRepository;
use WorldlineOP\PrestaShop\Repository\TransactionRepository;
use WorldlineOP\PrestaShop\Sdk\ClientFactory;
use WorldlineOP\PrestaShop\Utils\Decimal;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class TransactionPresenter
 * @package WorldlineOP\PrestaShop\Presenter
 */
class WebhookEventPresenter implements PresenterInterface
{
    const EVENTS_PAYMENT_AUTHORIZED = [
        'payment.pending_approval',
        'payment.pending_completion',
        'payment.pending_capture',
    ];
    const EVENTS_PAYMENT_ACCEPTED = ['payment.captured'];
    const EVENTS_PAYMENT_PENDING = ['payment.authorization_requested'];
    const EVENTS_REFUNDED = ['payment.refunded'];
    const EVENTS_PAYMENT_CANCELLED = ['payment.cancelled'];
    const EVENTS_PAYMENT_REJECTED = ['payment.rejected'];

    const PAYMENT_METHODS_TOKEN = ['card', 'redirect'];
    const PAYMENT_METHOD_CARD = 'card';

    const MAX_DELAY_BEFORE_REFUND = 7;

    /** @var Worldlineop $module */
    private $module;

    /** @var ClientFactory $merchantClient */
    private $merchantClientFactory;

    /** @var SettingsLoader $settingsLoader */
    private $settingsLoader;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /** @var Cart $cart */
    private $cart;

    /** @var array $data */
    private $data;

    /**
     * WebhookEventPresenter constructor.
     * @param Worldlineop    $module
     * @param ClientFactory  $merchantClientFactory
     * @param SettingsLoader $settingsLoader
     * @param LoggerFactory  $loggerFactory
     */
    public function __construct(
        Worldlineop $module,
        ClientFactory $merchantClientFactory,
        SettingsLoader $settingsLoader,
        LoggerFactory $loggerFactory
    ) {
        $this->module = $module;
        $this->merchantClientFactory = $merchantClientFactory;
        $this->settingsLoader = $settingsLoader;
        $this->logger = $loggerFactory->setChannel('Webhooks');
        $this->data = ['validateOrder' => false, 'updateOrderStatus' => false];
    }

    /**
     * @param WebhooksEvent $event
     */
    public function handlePending($event)
    {
        $paymentEvents = array_merge(
            self::EVENTS_PAYMENT_AUTHORIZED,
            self::EVENTS_PAYMENT_ACCEPTED
        );

        if (in_array($event->getType(), $paymentEvents)) {
            $this->logger->debug('Sleeeeep');
            sleep(6);
        }
    }

    /**
     * @param WebhooksEvent|bool $event
     * @param int|bool           $idShop
     * @return array
     * @throws \PrestaShopException
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public function present($event = false, $idShop = false)
    {
        $paymentEvents = array_merge(
            self::EVENTS_PAYMENT_PENDING,
            self::EVENTS_PAYMENT_AUTHORIZED,
            self::EVENTS_PAYMENT_ACCEPTED,
            self::EVENTS_PAYMENT_CANCELLED,
            self::EVENTS_PAYMENT_REJECTED
        );
        if (in_array($event->getType(), self::EVENTS_REFUNDED)) {
            $this->presentRefund($event->getRefund(), $idShop);
        } elseif (in_array($event->getType(), $paymentEvents)) {
            $this->presentPayment($event->getPayment(), $event->getType(), $idShop);
        }
        $this->logger->debug('Returning data', ['data' => $this->data]);

        return $this->data;
    }

    /**
     * @param RefundResponse $refundResponse
     * @param int            $idShop
     * @throws \PrestaShopException
     */
    private function presentRefund(RefundResponse $refundResponse, $idShop)
    {
        $merchantReferenceFull = $refundResponse->getRefundOutput()->getReferences()->getMerchantReference();
        $merchantReferenceParts = explode('-', $merchantReferenceFull);
        $this->cart = new Cart((int) $merchantReferenceParts[0]);
        if (!Validate::isLoadedObject($this->cart)) {
            $this->logger->error('Cart cannot be loaded', ['merchantReference' => $merchantReferenceFull]);

            return;
        }
        if ($this->cart->id_shop != $idShop) {
            $this->logger->error('Cart shop does not match webhook event shop', ['id_shop' => $idShop]);

            return;
        }
        $idOrder = Order::getOrderByCartId($this->cart->id);
        $order = new Order((int) $idOrder);
        if (!Validate::isLoadedObject($order)) {
            $this->logger->error('Cart cannot be loaded', ['merchantReference' => $merchantReferenceFull]);

            return;
        }
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
        /** @var \WorldlineopTransaction $transaction */
        $transaction = $transactionRepository->findByIdOrder($order->id);
        $merchantReference = strstr($refundResponse->getId(), '_', true);
        if (false === $transaction ||
            (strstr($transaction->reference, '_', true) !== $merchantReference && false !== $merchantReference)
        ) {
            $this->logger->error('Could not find transaction', ['merchantReference' => $merchantReferenceFull]);

            return;
        }
        $this->data = [
            'validateOrder' => false,
            'updateOrderStatus' => true,
            'order' => [
                'ids' => Tools::getOrderIdsByIdCart($order->id_cart),
            ],
            'idOrderState' => \Configuration::get('PS_OS_REFUND'),
            'sendMail' => \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID') == \Configuration::get('PS_OS_REFUND'),
            'payments' => [
                'hasPayments' => $order->getOrderPayments(),
                'merchantReference' => $merchantReference,
            ],
        ];
        $this->logger->debug('Refund event. Update order state to ID '.\Configuration::get('PS_OS_REFUND'));

        return;
    }

    /**
     * @param PaymentResponse $paymentResponse
     * @param string          $eventType
     * @param int             $idShop
     * @throws \Exception
     * @throws \PrestaShopException
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    private function presentPayment(PaymentResponse $paymentResponse, $eventType, $idShop)
    {
        $paymentOutput = $paymentResponse->getPaymentOutput();
        $merchantReferenceFull = $paymentOutput->getReferences()->getMerchantReference();
        $merchantReferenceParts = explode('-', $merchantReferenceFull);
        $this->cart = new Cart((int) $merchantReferenceParts[0]);
        if (!Validate::isLoadedObject($this->cart)) {
            $this->logger->error('Cart cannot be loaded', ['merchantReference' => $merchantReferenceFull]);

            return;
        }
        if ($this->cart->id_shop != $idShop) {
            $this->logger->error('Cart shop does not match webhook event shop', ['id_shop' => $idShop]);

            return;
        }
        $idShop = $this->cart->id_shop;
        $settings = $this->settingsLoader->setContext($idShop);
        $this->merchantClientFactory->setSettings($settings);
        $merchantClient = $this->merchantClientFactory->getMerchant();
        $idOrder = Order::getOrderByCartId($this->cart->id);
        $order = new Order((int) $idOrder);
        if (in_array($eventType, self::EVENTS_PAYMENT_ACCEPTED)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->successOrderStateId;
        } elseif (in_array($eventType, self::EVENTS_PAYMENT_AUTHORIZED)) {
            $idOrderState = \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID');
        } elseif (in_array($eventType, self::EVENTS_PAYMENT_PENDING)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->pendingOrderStateId;
        } elseif (in_array($eventType, self::EVENTS_PAYMENT_CANCELLED)) {
            $idOrderState = \Configuration::getGlobalValue('PS_OS_CANCELED');
        } elseif (in_array($eventType, self::EVENTS_PAYMENT_REJECTED)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->errorOrderStateId;
        } else {
            return;
        }
        if (Validate::isLoadedObject($order)) {
            $this->logger->debug('Order already exists', ['id_order' => $order->id]);
            /** @var TransactionRepository $transactionRepository */
            $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
            /** @var \WorldlineopTransaction $transaction */
            $transaction = $transactionRepository->findByIdOrder($order->id);
            $merchantReference = strstr($paymentResponse->getId(), '_', true);
            if (false === $transaction ||
                (strstr($transaction->reference, '_', true) !== $merchantReference && false !== $merchantReference)
            ) {
                $this->logger->error('Cannot find transaction for order '.$order->id);

                return;
            }
            if (\Configuration::getGlobalValue('PS_OS_CANCELED') == $order->current_state) {
                $cancelState = $order->getHistory(null, $order->current_state);
                $cancelledDate = $cancelState[0]['date_add'];
                $now = date('Y-m-d H:i:s');
                $datetime1 = new DateTime($cancelledDate);
                $datetime2 = new DateTime($now);
                $interval = $datetime1->diff($datetime2);
                if ($interval->format('%a') >= self::MAX_DELAY_BEFORE_REFUND) {
                    try {
                        $paymentDetails = $merchantClient->payments()->getPaymentDetails($transaction->reference);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());

                        return;
                    }
                    if ($paymentDetails->getStatusOutput()->getIsRefundable()) {
                        $refundRequest = new RefundRequest();
                        $amountOfMoney = new AmountOfMoney();
                        $amount = $paymentDetails->getPaymentOutput()->getAmountOfMoney();
                        $amountOfMoney->setAmount((int) Decimal::divide((string) $amount->getAmount(), '100')->getIntegerPart());
                        $amountOfMoney->setCurrencyCode($amount->getCurrencyCode());
                        $refundRequest->setAmountOfMoney($amountOfMoney);
                        try {
                            $merchantClient->payments()->refundPayment($transaction->reference, $refundRequest);
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());

                            return;
                        }
                    } else {
                        $this->logger->error('Transaction cannot be refunded');

                        return;
                    }
                }
            }
            if (!count($order->getHistory($this->cart->id_lang, $idOrderState)) &&
                !in_array($eventType, self::EVENTS_PAYMENT_PENDING)
            ) {
                /** @var TransactionRepository $transactionRepository */
                $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
                /** @var \WorldlineopTransaction $transaction */
                $transaction = $transactionRepository->findByIdOrder($order->id);
                if (false === $transaction) {
                    $this->logger->error('Transaction cannot be retrieved');

                    return;
                }
                if ($order->current_state == $settings->advancedSettings->paymentSettings->errorOrderStateId) {
                    $transaction->reference = pSQL($paymentResponse->getId());
                    $transactionRepository->save($transaction);
                }
                $merchantReference = strstr($transaction->reference, '_', true);
                $this->data = [
                    'validateOrder' => false,
                    'updateOrderStatus' => true,
                    'order' => [
                        'ids' => Tools::getOrderIdsByIdCart($order->id_cart),
                    ],
                    'idOrderState' => $idOrderState,
                    'sendMail' => \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID') == $idOrderState,
                    'payments' => [
                        'hasPayments' => $order->getOrderPayments(),
                        'merchantReference' => $merchantReference,
                    ],
                ];
                $this->logger->debug('Update order state to ID '.$idOrderState);

                return;
            }
        } else {
            $this->logger->debug('Order does not exist', ['merchantReference' => $merchantReferenceFull]);
            if (in_array($eventType, array_merge(self::EVENTS_PAYMENT_CANCELLED, self::EVENTS_PAYMENT_REJECTED))) {
                $this->logger->debug('Cancellation or rejection without order');

                return;
            }
            $order = new Order();
            $merchantReference = $paymentOutput->getReferences()->getMerchantReference();
            $currencyCode = $paymentOutput->getAmountOfMoney()->getCurrencyCode();
            $paymentSpecificOutput = $this->getPaymentSpecificOutput($paymentOutput->getPaymentMethod(), $paymentOutput);
            $productId = $paymentSpecificOutput->getPaymentProductId();
            $paymentProductParams = new GetPaymentProductParams();
            $paymentProductParams->setCurrencyCode($currencyCode);
            $paymentProductParams->setCountryCode(
                Country::getIsoById((new Address($this->cart->id_address_invoice))->id_country)
            );
            $paymentMethodText = $this->module->l('Worldline Online Payments', 'WebhookEventPresenter');
            try {
                $paymentProduct = $merchantClient->products()->getPaymentProduct($productId, $paymentProductParams);
                $paymentMethodText .= ' ['.$paymentProduct->getDisplayHints()->getLabel().']';
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $token = ['needSave' => false];
            /** @var \WorldlineOP\PrestaShop\Repository\HostedCheckoutRepository $hostedCheckoutRepository */
            $hostedCheckoutRepository = $this->module->getService('worldlineop.repository.hosted_checkout');
            /** @var \WorldlineOP\PrestaShop\Repository\CreatedPaymentRepository $createdPaymentRepository */
            $createdPaymentRepository = $this->module->getService('worldlineop.repository.created_payment');

            /** @var HostedCheckout $hostedCheckout */
            $hostedCheckout = $hostedCheckoutRepository->findByMerchantReference($merchantReference);
            /** @var CreatedPayment $createdPayment */
            $createdPayment = $createdPaymentRepository->findByMerchantReference($merchantReference);

            if (!$hostedCheckout && !$createdPayment) {
                $this->logger->debug(sprintf('Merchant reference %s not found', $merchantReference));

                return;
            }
            if ($hostedCheckout) {
                $this->logger->debug('Payment has been made through Hosted Checkout Page');
                $this->logger->debug('Checkout Session found');
                if (in_array($paymentOutput->getPaymentMethod(), self::PAYMENT_METHODS_TOKEN)) {
                    if ($paymentSpecificOutput->getToken()) {
                        $this->logger->debug('Token field is not empty.');
                        /** @var \Ingenico\Direct\Sdk\Merchant\MerchantClient $merchantClient */
                        $merchantClient = $this->module->getService('worldlineop.sdk.client');
                        try {
                            /** @var \Ingenico\Direct\Sdk\Domain\TokenResponse $tokenResponse */
                            $tokenResponse = $merchantClient->tokens()
                                                            ->getToken($paymentSpecificOutput->getToken());
                        } catch (\Exception $e) {
                            $this->logger->error(
                                'Could not fetch token',
                                ['message' => $e->getMessage(), 'tokenValue' => $paymentSpecificOutput->getToken()]
                            );
                        }
                        if (isset($tokenResponse) &&
                            $tokenResponse->getId() &&
                            false === $tokenResponse->getIsTemporary()
                        ) {
                            $this->logger->debug('Token is not temporary. Need save.');
                            $token = [
                                'needSave' => true,
                                'value' => $paymentSpecificOutput->getToken(),
                                'idShop' => $this->cart->id_shop,
                            ];
                            if (self::PAYMENT_METHOD_CARD === $paymentOutput->getPaymentMethod()) {
                                $token['cardNumber'] = $paymentSpecificOutput->getCard()->getCardNumber();
                                $token['expiryDate'] = $paymentSpecificOutput->getCard()->getExpiryDate();
                            }
                        }
                    }
                }
            } elseif ($createdPayment) {
                $this->logger->debug('Payment has been made through Tokenization Page');
                $this->logger->debug('CreatedPayment found');
            } else {
                $this->logger->debug('Could not find hosted or htp', ['merchantReference' => $merchantReferenceFull]);

                return;
            }
            $this->data = [
                'validateOrder' => true,
                'updateOrderStatus' => false,
                'token' => $token,
                'cartDetails' => [
                    'idCart' => $this->cart->id,
                    'total' => Decimal::divide((string) $paymentOutput->getAmountOfMoney()->getAmount(), '100')
                                      ->round(2),
                    'secureKey' => $this->cart->secure_key,
                    'idCustomer' => $this->cart->id_customer,
                ],
                'transaction' => [
                    'productId' => $productId,
                    'paymentMethod' => $paymentMethodText,
                    'details' => [
                        'transaction_id' => strstr($paymentResponse->getId(), '_', true),
                    ],
                    'idCurrency' => Currency::getIdByIsoCode($currencyCode),
                    'merchantReference' => $paymentResponse->getId(),
                ],
                'order' => [
                    'ids' => Tools::getOrderIdsByIdCart($order->id_cart),
                ],
                'idOrderState' => $idOrderState,
                'sendMail' => \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID') == $idOrderState,
            ];

            return;
        }
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
}
