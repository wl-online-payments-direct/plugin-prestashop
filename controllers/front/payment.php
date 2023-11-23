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

use OnlinePayments\Sdk\ResponseException;
use PrestaShop\Decimal\Number;
use WorldlineOP\PrestaShop\Repository\TokenRepository;

/**
 * Class WorldlineopPaymentModuleFrontController
 */
class WorldlineopPaymentModuleFrontController extends ModuleFrontController
{
    const MERCHANT_ACTION_REDIRECT = 'REDIRECT';

    const TOKEN_STATUS_CREATED = 'CREATED';
    const TOKEN_STATUS_UPDATED = 'UPDATED';

    /** @var Worldlineop */
    public $module;

    /** @var \Monolog\Logger */
    public $logger;

    /**
     * @throws Exception
     */
    public function displayAjaxCreatePayment()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('CreatePayment');

        $cart = $this->context->cart;
        $hostedTokenizationId = Tools::getValue('hostedTokenizationId');
        $totalCartPost = new Number(Tools::getValue('worldlineopTotalCartCents'));
        $cartCurrencyCodePost = Tools::getValue('worldlineopCartCurrencyCode');
        $totalCart = \WorldlineOP\PrestaShop\Utils\Tools::getRoundedAmountInCents($cart->getOrderTotal(),
            \WorldlineOP\PrestaShop\Utils\Tools::getIsoCurrencyCodeById($cart->id_currency));
        $cartCurrencyCode = \WorldlineOP\PrestaShop\Utils\Tools::getIsoCurrencyCodeById($cart->id_currency);
        if ($totalCart !== $totalCartPost->getIntegerPart() || $cartCurrencyCode !== $cartCurrencyCodePost) {
            $this->logger->error(
                'Cart currency/amount does not match context',
                [
                    'cartCurrency' => $cartCurrencyCode,
                    'cartCurrencyPost' => $cartCurrencyCodePost,
                    'totalCart' => $totalCart,
                    'totalCartPost' => $totalCartPost->getIntegerPart(),
                ]
            );
            // @formatter:off
            exit(json_encode([
                'success' => false,
                'message' => $this->module->l('An error occurred while processing the payment.', 'payment'),
            ]));
            // @formatter:on
        }

        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        try {
            $hostedTokenizationResponse = $merchantClient->hostedTokenization()
                ->getHostedTokenization($hostedTokenizationId);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['hostedTokenizationId' => $hostedTokenizationId]);
            // @formatter:off
            exit(json_encode([
                'success' => false,
                'message' => $this->module->l('An error occurred while processing the payment.', 'payment'),
            ]));
            // @formatter:on
        }

        $this->logger->debug(
            'HostedTokenization Response',
            ['json' => json_decode($hostedTokenizationResponse->toJson(), true)]
        );
        $tokenId = $hostedTokenizationResponse->getToken()->getId();
        $ccForm = Tools::getValue('ccForm');

        if (false === $hostedTokenizationResponse->getToken()->getIsTemporary()
            && (self::TOKEN_STATUS_CREATED === $hostedTokenizationResponse->getTokenStatus()
                || self::TOKEN_STATUS_UPDATED === $hostedTokenizationResponse->getTokenStatus()
            )
        ) {
            /** @var TokenRepository $tokenRepository */
            $tokenRepository = $this->module->getService('worldlineop.repository.token');
            $token = $tokenRepository->findByCustomerIdToken($this->context->customer->id, $tokenId);
            if (false === $token) {
                $token = new WorldlineopToken();
            }
            $cardData = $hostedTokenizationResponse->getToken()->getCard()->getData()->getCardWithoutCvv();
            $token->id_customer = (int) $this->context->customer->id;
            $token->id_shop = (int) $this->context->shop->id;
            $token->product_id = PSQL($hostedTokenizationResponse->getToken()->getPaymentProductId());
            $token->card_number = pSQL($cardData->getCardNumber());
            $token->expiry_date = pSQL($cardData->getExpiryDate());
            $token->value = pSQL($tokenId);
            $token->secure_key = pSQL($this->context->customer->secure_key);
            $tokenRepository->save($token);
        }

        /** @var \WorldlineOP\PrestaShop\Builder\PaymentRequestDirector $hostedCheckoutDirector */
        $hostedCheckoutDirector = $this->module->getService('worldlineop.payment_request.director');
        try {
            $paymentRequest = $hostedCheckoutDirector->buildPaymentRequest($tokenId, $ccForm);
            $this->module->logger->debug('IframeHostedTokenizationRequest', ['json' => json_decode($paymentRequest->toJson(), true)]);
            $paymentResponse = $merchantClient->payments()
                ->createPayment($paymentRequest);
            $this->logger->debug('IframeHostedTokenizationResponse', ['json' => json_decode($paymentResponse->toJson(), true)]);
        } catch (ResponseException $re) {
            $this->logger->debug('IframeHostedTokenizationResponse', ['json' => json_decode($re->getResponse()->toJson(), true)]);
            // @formatter:off
            exit(json_encode([
                'success' => false,
                'message' => $this->module->l('An error occurred while processing the payment.', 'payment'),
            ]));
            // @formatter:on
        } catch (Exception $e) {
            $this->logger->debug('IframeHostedTokenizationResponse',
                ['json' => json_decode($e->getResponse()->toJson(), true)]);
            // @formatter:off
            exit(json_encode([
                'success' => false,
                'message' => $this->module->l('An error occurred while processing the payment.', 'payment'),
            ]));
            // @formatter:on
        }
        /** @var \WorldlineOP\PrestaShop\Repository\CreatedPaymentRepository $createdPaymentRepository */
        $createdPaymentRepository = $this->module->getService('worldlineop.repository.created_payment');
        $this->logger->debug('Payment Response', ['response' => json_decode($paymentResponse->toJson(), true)]);
        $createdPayment = new CreatedPayment();
        $createdPayment->id_cart = (int) $cart->id;
        $createdPayment->payment_id = pSQL($paymentResponse->getPayment()->getId());
        $createdPayment->merchant_reference = pSQL($paymentResponse->getPayment()->getPaymentOutput()->getReferences()
            ->getMerchantReference());
        $createdPayment->status = pSQL($paymentResponse->getPayment()->getStatus());
        $merchantAction = $paymentResponse->getMerchantAction();
        if (null !== $merchantAction && $merchantAction->getActionType() === self::MERCHANT_ACTION_REDIRECT) {
            $createdPayment->returnmac = pSQL($merchantAction->getRedirectData()->getRETURNMAC());
            $return = [
                'success' => true,
                'needRedirect' => true,
                'redirectUrl' => $merchantAction->getRedirectData()->getRedirectURL(),
            ];
        } else {
            $return = [
                'success' => true,
                'needRedirect' => true,
                'redirectUrl' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'redirect',
                    ['action' => 'redirectReturnInternalIframe', 'paymentId' => $createdPayment->payment_id]
                ),
            ];
        }
        try {
            $createdPaymentRepository->save($createdPayment);
        } catch (Exception $e) {
            $this->logger->error('Cannot save CreatedPayment object', ['message' => $e->getMessage()]);
            // @formatter:off
            $return = [
                'success' => false,
                'message' => $this->module->l('An unexpected error occurred. Please contact our customer service.', 'payment'),
            ];
            // @formatter:on
        }
        exit(json_encode($return));
    }

    /**
     * @return void
     */
    public function displayAjaxFormatSurchargeAmounts()
    {
        try {
            $return = [
                'success' => true,
                'formattedInitialAmount' => \WorldlineOP\PrestaShop\Utils\Tools::getRoundedAmountFromCents(Tools::getValue('initialAmount'), Tools::getValue('initialCurrency')) . ' ' . Tools::getValue('initialCurrency'),
                'formattedSurchargeAmount' => \WorldlineOP\PrestaShop\Utils\Tools::getRoundedAmountFromCents(Tools::getValue('surchargeAmount'), Tools::getValue('surchargeCurrency')) . ' ' . Tools::getValue('surchargeCurrency'),
                'formattedTotalAmount' => \WorldlineOP\PrestaShop\Utils\Tools::getRoundedAmountFromCents(Tools::getValue('totalAmount'), Tools::getValue('totalCurrency')) . ' ' . Tools::getValue('totalCurrency'),
            ];
        } catch (Exception $e) {
            $return = [
                'success' => false,
            ];
        }

        exit(json_encode($return));
    }
}
