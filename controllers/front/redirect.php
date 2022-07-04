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

use WorldlineOP\PrestaShop\Configuration\Entity\Settings;

/**
 * Class WorldlineopRedirectModuleFrontController
 */
class WorldlineopRedirectModuleFrontController extends ModuleFrontController
{
    const STATUS_CANCELLED = 'CANCELLED_BY_CONSUMER';
    const STATUS_CREATED = 'PAYMENT_CREATED';
    const STATUS_CATEGORY_REJECTED = 'REJECTED';
    const STATUS_CATEGORY_SUCCESSFUL = 'SUCCESSFUL';

    const ACTIONS = ['redirectReturnHosted', 'redirectReturnIframe', 'redirectReturnInternalIframe'];

    /** @var Worldlineop $module */
    public $module;

    /** @var \Monolog\Logger $logger */
    public $logger;

    /** @var \WorldlineOP\PrestaShop\Repository\HostedCheckoutRepository $hostedCheckoutRepository */
    private $hostedCheckoutRepository;

    /** @var \WorldlineOP\PrestaShop\Repository\CreatedPaymentRepository $createdPaymentRepository */
    private $createdPaymentRepository;

    /** @var \Ingenico\Direct\Sdk\Merchant\MerchantClient $merchantClient */
    private $merchantClient;

    /** @var CartChecksum $cartChecksum */
    private $cartChecksum;

    /**
     * WorldlineopRedirectModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->hostedCheckoutRepository = $this->module->getService('worldlineop.repository.hosted_checkout');
        $this->createdPaymentRepository = $this->module->getService('worldlineop.repository.created_payment');
        $this->merchantClient = $this->module->getService('worldlineop.sdk.client');
        $this->cartChecksum = $this->module->getService('worldlineop.checksum.cart');
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        $this->setTemplate('module:worldlineop/views/templates/front/redirect.tpl');
        $action = Tools::getValue('action');
        if (!in_array($action, self::ACTIONS)) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, ['step' => 3]));
        }

        $this->context->smarty->assign([
            'img_path' => sprintf(__PS_BASE_URI__.'modules/%s/views/img/', $this->module->name),
            'worldlineopRedirectController' => $this->context->link->getModuleLink(
                $this->module->name,
                'redirect',
                ['action' => $action]
            ),
            'returnMac' => Tools::getValue('RETURNMAC'),
            'hostedCheckoutId' => Tools::getValue('hostedCheckoutId'),
            'paymentId' => Tools::getValue('paymentId'),
            'worldlineopCustomerToken' => Tools::getToken(),
        ]);

        return parent::display();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    public function displayAjaxRedirectExternal()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('RedirectExternal');
        $cart = $this->context->cart;
        $idProduct = Tools::getValue('productId');
        $idToken = Tools::getValue('tokenId');
        if (false === $idToken) {
            $tokenValue = false;
        } else {
            /** @var \WorldlineOP\PrestaShop\Repository\TokenRepository $tokenRepository */
            $tokenRepository = $this->module->getService('worldlineop.repository.token');
            $token = $tokenRepository->findById($idToken);
            if (false === $token ||
                $token->secure_key !== $this->context->customer->secure_key ||
                (int) $token->id_customer !== $this->context->customer->id
            ) {
                Tools::redirect($this->context->link->getPageLink('order', null, null, ['step' => 3]));
            }
            $tokenValue = $token->value;
        }
        if (false !== Order::getOrderByCartId($cart->id)) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, ['step' => 3]));
        }

        /** @var HostedCheckout $hostedCheckout */
        $hostedCheckout = $this->hostedCheckoutRepository->findByChecksumIdCartIdProductIdToken(
            $this->cartChecksum->generateChecksum($cart),
            $cart->id,
            $idProduct,
            $idToken
        );
        if (false !== $hostedCheckout && Validate::isLoadedObject($hostedCheckout)) {
            $maxTime = new DateTime($hostedCheckout->date_add);
            $maxTime->add(new DateInterval('PT3H'));
            $now = new DateTime();
            if ($now < $maxTime) {
                try {
                    $existingHostedCheckoutResponse = $this->merchantClient->hostedCheckout()
                                                                           ->getHostedCheckout($hostedCheckout->session_id);
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
                if (isset($existingHostedCheckoutResponse)) {
                    if (self::STATUS_CANCELLED === $existingHostedCheckoutResponse->getStatus() ||
                        self::STATUS_CATEGORY_REJECTED === $existingHostedCheckoutResponse->getCreatedPaymentOutput()
                                                                                          ->getPaymentStatusCategory()
                    ) {
                        $this->hostedCheckoutRepository->delete($hostedCheckout);
                        $hostedCheckout = new HostedCheckout();
                    } else {
                        Tools::redirect(Settings::DEFAULT_SUBDOMAIN.$hostedCheckout->partial_redirect_url);
                    }
                }
            }
        } else {
            $hostedCheckout = new HostedCheckout();
        }

        /** @var \WorldlineOP\PrestaShop\Builder\PaymentRequestDirector $hostedCheckoutDirector */
        $hostedCheckoutDirector = $this->module->getService('worldlineop.hosted_payment_request.director');
        try {
            $hostedCheckoutRequest = $hostedCheckoutDirector->buildHostedPaymentRequest($idProduct, $tokenValue);
            $this->logger->debug(
                'Creating Hosted Payment Request',
                ['request' => json_decode($hostedCheckoutRequest->toJson(), true)]
            );
            $hostedCheckoutResponse = $this->merchantClient->hostedCheckout()
                                                           ->createHostedCheckout($hostedCheckoutRequest);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            Tools::redirect($this->context->link->getPageLink(
                'order',
                null,
                null,
                ['step' => 3, 'worldlineopDisplayPaymentTopMessage' => 1]
            ));

            return;
        }

        $hostedCheckout->checksum = pSQL($this->cartChecksum->generateChecksum($cart));
        $hostedCheckout->session_id = pSQL($hostedCheckoutResponse->getHostedCheckoutId());
        $hostedCheckout->id_cart = (int) $cart->id;
        $hostedCheckout->id_token = (int) $idToken;
        $hostedCheckout->id_payment_product = (int) $idProduct;
        $hostedCheckout->partial_redirect_url = pSQL($hostedCheckoutResponse->getPartialRedirectUrl());
        $hostedCheckout->merchant_reference = pSQL($hostedCheckoutResponse->getMerchantReference());
        $hostedCheckout->returnmac = pSQL($hostedCheckoutResponse->getRETURNMAC());
        $hostedCheckout->date_add = date('Y-m-d H:i:s');
        $this->hostedCheckoutRepository->save($hostedCheckout);
        Tools::redirect(Settings::DEFAULT_SUBDOMAIN.$hostedCheckout->partial_redirect_url);
    }

    /**
     * @throws PrestaShopException
     */
    public function displayAjaxRedirectReturnHosted()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('Redirect');

        /** @var HostedCheckout $hostedCheckout */
        $hostedCheckout = $this->hostedCheckoutRepository->findByReturnMacHostedCheckoutId(
            Tools::getValue('RETURNMAC'),
            Tools::getValue('hostedCheckoutId')
        );

        if (!Validate::isLoadedObject($hostedCheckout)) {
            $this->dieOrderStep3();
        }

        try {
            /** @var \Ingenico\Direct\Sdk\Domain\GetHostedCheckoutResponse $hostedCheckoutResponse */
            $hostedCheckoutResponse = $this->merchantClient->hostedCheckout()
                                                           ->getHostedCheckout($hostedCheckout->session_id);
        } catch (Exception $e) {
            $this->dieOrderStep3();
        }

        if (self::STATUS_CANCELLED === $hostedCheckoutResponse->getStatus()) {
            $this->hostedCheckoutRepository->delete($hostedCheckout);
            $this->dieOrderStep3(false);
        }
        if (self::STATUS_CREATED === $hostedCheckoutResponse->getStatus()) {
            if (self::STATUS_CATEGORY_REJECTED === $hostedCheckoutResponse->getCreatedPaymentOutput()
                                                                          ->getPaymentStatusCategory()
            ) {
                $this->hostedCheckoutRepository->delete($hostedCheckout);
                $this->dieOrderStep3();
            }
        }

        $cart = new Cart((int) $hostedCheckout->id_cart);
        if (false !== Order::getOrderByCartId($cart->id)) {
            $customer = new Customer((int) $cart->id_customer);
            die(json_encode([
                'redirectUrl' => $this->context->link->getPageLink(
                    'order-confirmation',
                    null,
                    null,
                    [
                        'id_cart' => $hostedCheckout->id_cart,
                        'id_module' => $this->module->id,
                        'key' => $customer->secure_key,
                    ]
                ),
            ]));
        }
        die();
    }

    /**
     * @throws PrestaShopException
     */
    public function displayAjaxRedirectReturnIframe()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('RedirectIframe');

        /** @var CreatedPayment $createdPayment */
        $createdPayment = $this->createdPaymentRepository->findByReturnMacPaymentId(
            Tools::getValue('RETURNMAC'),
            Tools::getValue('paymentId')
        );

        $this->returnRedirectIframe($createdPayment);
    }

    /**
     * @throws PrestaShopException
     */
    public function displayAjaxRedirectReturnInternalIframe()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('RedirectInternalIframe');

        /** @var CreatedPayment $createdPayment */
        $createdPayment = $this->createdPaymentRepository->findByPaymentId(
            Tools::getValue('paymentId')
        );

        $this->returnRedirectIframe($createdPayment);
    }

    /**
     * @param false|CreatedPayment $createdPayment
     */
    public function returnRedirectIframe($createdPayment)
    {
        if (!Validate::isLoadedObject($createdPayment)) {
            $this->dieOrderStep3();
        }

        try {
            $paymentResponse = $this->merchantClient->payments()
                                                    ->getPayment($createdPayment->payment_id);
        } catch (Exception $e) {
            $this->logger->error('Could not retrieve payment', ['message' => $e->getMessage()]);
            $this->dieOrderStep3();
        }

        $cart = new Cart((int) $createdPayment->id_cart);
        $customer = new Customer((int) $cart->id_customer);
        $statusCategory = $paymentResponse->getStatusOutput()->getStatusCategory();
        switch ($statusCategory) {
            case 'UNSUCCESSFUL':
                $this->dieOrderStep3();
                break;
            case 'COMPLETED':
            case 'PENDING_MERCHANT':
                $this->dieOrderConfirmation($cart, $customer);
                break;
            case 'PENDING_CONNECT_OR_3RD_PARTY':
                die();
                break;
            default:
                die();
                break;
        }
    }

    /**
     * @param bool $displayErrorMessage
     */
    public function dieOrderStep3($displayErrorMessage = true)
    {
        $params = ['step' => 3];
        if (true === $displayErrorMessage) {
            $params['worldlineopDisplayPaymentTopMessage'] = 1;
        }
        die(json_encode([
            'redirectUrl' => $this->context->link->getPageLink('order', null, null, $params),
        ]));
    }

    /**
     * @param Cart $cart
     * @param Customer $customer
     */
    public function dieOrderConfirmation($cart, $customer)
    {
        if (false !== Order::getOrderByCartId($cart->id)) {
            die(json_encode([
                'redirectUrl' => $this->context->link->getPageLink(
                    'order-confirmation',
                    null,
                    null,
                    [
                        'id_cart' => $cart->id,
                        'id_module' => $this->module->id,
                        'key' => $customer->secure_key,
                    ]
                ),
            ]));
        }
        die();
    }
}
