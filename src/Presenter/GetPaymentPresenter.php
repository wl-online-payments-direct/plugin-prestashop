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

use Address;
use Cart;
use Country;
use CreatedPayment;
use Currency;
use HostedCheckout;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\PaymentOutput;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductParams;
use Order;
use Validate;
use Worldlineop;
use WorldlineOP\PrestaShop\Configuration\Loader\SettingsLoader;
use WorldlineOP\PrestaShop\Logger\LoggerFactory;
use WorldlineOP\PrestaShop\Repository\TransactionRepository;
use WorldlineOP\PrestaShop\Sdk\ClientFactory;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class GetPaymentPresenter
 */
class GetPaymentPresenter implements PresenterInterface
{
    const PAYMENT_METHODS_TOKEN = ['card', 'redirect'];
    const PAYMENT_METHOD_CARD = 'card';

    const STATUS_ACCEPTED = ['CAPTURED'];
    const STATUS_AUTHORIZED = ['PENDING_CAPTURE'];
    const STATUS_PENDING = ['AUTHORIZATION_REQUESTED', 'CAPTURE_REQUESTED'];
    const STATUS_CANCELLED = ['CANCELLED'];
    const STATUS_REJECTED = ['REJECTED'];

    const MAX_DELAY_BEFORE_REFUND = 7;

    /** @var Worldlineop */
    private $module;

    /** @var ClientFactory */
    private $merchantClientFactory;

    /** @var SettingsLoader */
    private $settingsLoader;

    /** @var \Monolog\Logger */
    private $logger;

    /** @var TransactionPresented */
    protected $presentedData;

    /** @var Cart */
    private $cart;

    /**
     * GetPaymentPresenter constructor.
     *
     * @param Worldlineop $module
     * @param ClientFactory $merchantClientFactory
     * @param SettingsLoader $settingsLoader
     * @param LoggerFactory $loggerFactory
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
        $this->logger = $loggerFactory->setChannel('GetPaymentPresenter');
        $this->presentedData = new TransactionPresented();
    }

    /**
     * @param PaymentResponse $paymentResponse
     * @param int $idShop
     *
     * @return TransactionPresented
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     * @throws \Exception
     */
    public function present($paymentResponse = false, $idShop = false)
    {
        $merchantReferenceFull = $paymentResponse->getPaymentOutput()->getReferences()->getMerchantReference();
        $merchantReferenceParts = explode('-', $merchantReferenceFull);
        $this->cart = new Cart((int) $merchantReferenceParts[0]);
        if (!Validate::isLoadedObject($this->cart)) {
            $this->logger->error('Cart cannot be loaded', ['merchantReference' => $merchantReferenceFull]);

            return $this->presentedData;
        }
        if ($this->cart->id_shop != $idShop) {
            $this->logger->error('Cart shop does not match webhook event shop', ['id_shop' => $idShop]);

            return $this->presentedData;
        }
        $idShop = $this->cart->id_shop;
        $settings = $this->settingsLoader->setContext($idShop);
        $this->merchantClientFactory->setSettings($settings);
        $idOrder = Order::getOrderByCartId($this->cart->id);
        $order = new Order((int) $idOrder);

        $paymentStatus = $paymentResponse->getStatus();
        if (in_array($paymentStatus, self::STATUS_ACCEPTED)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->successOrderStateId;
        } elseif (in_array($paymentStatus, self::STATUS_AUTHORIZED)) {
            $idOrderState = \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID');
        } elseif (in_array($paymentStatus, self::STATUS_PENDING)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->pendingOrderStateId;
        } elseif (in_array($paymentStatus, self::STATUS_CANCELLED)) {
            $idOrderState = \Configuration::getGlobalValue('PS_OS_CANCELED');
        } elseif (in_array($paymentStatus, self::STATUS_REJECTED)) {
            $idOrderState = $settings->advancedSettings->paymentSettings->errorOrderStateId;
        } else {
            return $this->presentedData;
        }
        $totalReceived = $paymentResponse->getPaymentOutput()->getAmountOfMoney()->getAmount();
        $totalPrestaShop = Tools::getRoundedAmountInCents($this->cart->getOrderTotal(true, Cart::BOTH, null, null, false, true), $paymentResponse->getPaymentOutput()->getAmountOfMoney()->getCurrencyCode());
        if ($totalPrestaShop != $totalReceived) {
            $this->logger->error('Amounts received/calculated does not match', ['received' => $totalReceived, 'calculated' => $totalPrestaShop]);
            $idOrderState = $settings->advancedSettings->paymentSettings->errorOrderStateId;
        }

        if (Validate::isLoadedObject($order)) {
            $this->logger->debug('Order already exists', ['id_order' => $order->id]);
            $this->presentExistingOrder($order, $idOrderState, $paymentResponse);
        } else {
            $this->logger->debug('Order does not exist', ['merchantReference' => $merchantReferenceFull]);
            if (in_array($paymentStatus, array_merge(self::STATUS_CANCELLED, self::STATUS_REJECTED))) {
                $this->logger->debug('Cancellation or rejection without order');

                return $this->presentedData;
            }
            $this->presentNewOrder($idOrderState, $paymentResponse);
        }

        return $this->presentedData;
    }

    /**
     * @param $idOrderState
     * @param PaymentResponse $paymentResponse
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    private function presentNewOrder($idOrderState, $paymentResponse)
    {
        $merchantClient = $this->merchantClientFactory->getMerchant();
        $paymentOutput = $paymentResponse->getPaymentOutput();
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
        $paymentMethodText = $this->module->l('Worldline Online Payments', 'GetPaymentPresenter');
        try {
            $paymentProduct = $merchantClient->products()->getPaymentProduct($productId, $paymentProductParams);
            $paymentMethodText .= ' [' . $paymentProduct->getDisplayHints()->getLabel() . ']';
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
            $token = $this->getTokenData($paymentOutput, $paymentSpecificOutput);
        } elseif ($createdPayment) {
            $this->logger->debug('Payment has been made through Tokenization Page');
            $this->logger->debug('CreatedPayment found');
        } else {
            $this->logger->debug('Could not find hosted or htp', ['merchantReference' => $merchantReference]);

            return;
        }
        $transactionId = strstr($paymentResponse->getId(), '_', true);
        if (false === $transactionId) {
            $transactionId = $paymentResponse->getId();
        }
        $pow = Tools::getCurrencyDecimalByIso($currencyCode);
        $this->presentedData->validateOrder = true;
        $this->presentedData->token = $token;
        $this->presentedData->cardDetails['idCart'] = $this->cart->id;
        $this->presentedData->cardDetails['total'] = Tools::getRoundedAmountFromCents($paymentOutput->getAmountOfMoney()->getAmount(), $paymentOutput->getAmountOfMoney()->getCurrencyCode());
        $this->presentedData->cardDetails['secureKey'] = $this->cart->secure_key;
        $this->presentedData->cardDetails['idCustomer'] = $this->cart->id_customer;
        $this->presentedData->transaction['productId'] = $productId;
        $this->presentedData->transaction['paymentMethod'] = $paymentMethodText;
        $this->presentedData->transaction['details']['transaction_id'] = $transactionId;
        $this->presentedData->transaction['idCurrency'] = Currency::getIdByIsoCode($currencyCode);
        $this->presentedData->transaction['merchantReference'] = $paymentResponse->getId();
        $this->presentedData->order['ids'] = Tools::getOrderIdsByIdCart($order->id_cart);
        $this->presentedData->idOrderState = $idOrderState;
        $this->presentedData->sendMail = \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID') == $idOrderState;
    }

    /**
     * @param Order $order
     * @param int $idOrderState
     * @param PaymentResponse $paymentResponse
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    private function presentExistingOrder($order, $idOrderState, $paymentResponse)
    {
        $merchantClient = $this->merchantClientFactory->getMerchant();
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
        /** @var \WorldlineopTransaction $transaction */
        $transaction = $transactionRepository->findByIdOrder($order->id);
        $merchantReference = strstr($paymentResponse->getId(), '_', true);
        if (false === $merchantReference) {
            $merchantReference = $paymentResponse->getId();
        }
        $idShop = $this->cart->id_shop;
        $settings = $this->settingsLoader->setContext($idShop);
        $transactionReference = strstr($transaction->reference, '_', true);
        if (false === $transactionReference) {
            $transactionReference = $transaction->reference;
        }
        if (false === $transaction || ($transactionReference !== $merchantReference && false !== $merchantReference)) {
            $this->logger->error('Cannot find transaction for order ' . $order->id);

            return;
        }
        if (\Configuration::getGlobalValue('PS_OS_CANCELED') == $order->current_state) {
            $cancelState = $order->getHistory(null, $order->current_state);
            $cancelledDate = $cancelState[0]['date_add'];
            $now = date('Y-m-d H:i:s');
            $datetime1 = new \DateTime($cancelledDate);
            $datetime2 = new \DateTime($now);
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
                    $amountOfMoney->setAmount($amount->getAmount());
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
            !in_array($paymentResponse->getStatus(), self::STATUS_PENDING)
        ) {
            /** @var TransactionRepository $transactionRepository */
            $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
            /** @var \WorldlineopTransaction $transaction */
            $transaction = $transactionRepository->findByIdOrder($order->id);
            if (false === $transaction) {
                $this->logger->error('Transaction cannot be retrieved');

                return;
            }
            if (false === strpos($paymentResponse->getId(), '_')) {
                $transactionId = $paymentResponse->getId();
                $receivedIteration = (int) substr($transactionId, -3);
            } else {
                $receivedIteration = substr($paymentResponse->getId(), strpos($paymentResponse->getId(), '_') + 1);
            }
            if (false === strpos($transaction->reference, '_')) {
                $transactionReference = $transaction->reference;
                $storedIteration = (int) substr($transactionReference, -3);
            } else {
                $storedIteration = substr($transaction->reference, strpos($transaction->reference, '_') + 1);
            }
            if (in_array($paymentResponse->getStatus(), self::STATUS_REJECTED) && (int) $receivedIteration < (int) $storedIteration) {
                $this->logger->error('Event received is older than last processed');

                return;
            }
            if ($order->current_state == $settings->advancedSettings->paymentSettings->errorOrderStateId) {
                $transaction->reference = pSQL($paymentResponse->getId());
                $transactionRepository->save($transaction);
            }
            $paymentOutput = $paymentResponse->getPaymentOutput();
            if (in_array($paymentResponse->getStatus(), array_merge(self::STATUS_ACCEPTED, self::STATUS_AUTHORIZED))) {
                $this->presentedData->token = $this->getTokenData($paymentOutput, $this->getPaymentSpecificOutput($paymentOutput->getPaymentMethod(), $paymentOutput));
                $this->presentedData->cardDetails['secureKey'] = $this->cart->secure_key;
            }
            if (in_array($paymentResponse->getStatus(), self::STATUS_ACCEPTED)) {
                if (($paymentOutput->getAcquiredAmount()->getAmount() !== $paymentOutput->getAmountOfMoney()->getAmount()) && ($paymentOutput->getSurchargeSpecificOutput() === null)) {
                    $this->logger->debug('Captured event for an incomplete partial payment');

                    return;
                }
            }
            $merchantReference = strstr($transaction->reference, '_', true);
            if (false === $merchantReference) {
                $merchantReference = $transaction->reference;
            }
            $this->presentedData->updateStatus = true;
            $this->presentedData->order['ids'] = Tools::getOrderIdsByIdCart($order->id_cart);
            $this->presentedData->idOrderState = $idOrderState;
            $this->presentedData->sendMail = \Configuration::getGlobalValue('WOP_AWAITING_CAPTURE_STATUS_ID') == $idOrderState;
            $this->presentedData->payments['hasPayments'] = $order->getOrderPayments();
            $this->presentedData->payments['merchantReference'] = $merchantReference;
            $this->logger->debug('Update order state to ID ' . $idOrderState);
        }
    }

    /**
     * @param PaymentOutput $paymentOutput
     * @param CardPaymentMethodSpecificOutput|MobilePaymentMethodSpecificOutput|RedirectPaymentMethodSpecificOutput $paymentSpecificOutput
     *
     * @return array
     */
    public function getTokenData($paymentOutput, $paymentSpecificOutput)
    {
        $token = ['needSave' => false];
        if (in_array($paymentOutput->getPaymentMethod(), self::PAYMENT_METHODS_TOKEN)) {
            if ($paymentSpecificOutput->getToken()) {
                $this->logger->debug('Token field is not empty.');
                /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
                $merchantClient = $this->module->getService('worldlineop.sdk.client');
                try {
                    /** @var \OnlinePayments\Sdk\Domain\TokenResponse $tokenResponse */
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

        return $token;
    }

    /**
     * @param string $paymentMethod
     * @param PaymentOutput $paymentOutput
     *
     * @return CardPaymentMethodSpecificOutput|MobilePaymentMethodSpecificOutput|RedirectPaymentMethodSpecificOutput
     */
    private function getPaymentSpecificOutput($paymentMethod, PaymentOutput $paymentOutput)
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
