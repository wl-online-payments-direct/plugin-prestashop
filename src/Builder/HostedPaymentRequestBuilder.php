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

namespace WorldlineOP\PrestaShop\Builder;

use Ingenico\Direct\Sdk\Domain\CardPaymentMethodSpecificInput;
use Ingenico\Direct\Sdk\Domain\HostedCheckoutSpecificInput;
use Ingenico\Direct\Sdk\Domain\MobilePaymentMethodHostedCheckoutSpecificInput;
use Ingenico\Direct\Sdk\Domain\Order;
use Ingenico\Direct\Sdk\Domain\OrderReferences;
use Ingenico\Direct\Sdk\Domain\PaymentProductFilter;
use Ingenico\Direct\Sdk\Domain\PaymentProductFiltersHostedCheckout;
use Ingenico\Direct\Sdk\Domain\ThreeDSecure;
use Language;
use RandomLib\Factory;
use SecurityLib\Strength;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethodsSettings;

/**
 * Class HostedPaymentRequestBuilder
 * @package WorldlineOP\PrestaShop\Builder
 */
class HostedPaymentRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @return HostedCheckoutSpecificInput|null
     * @throws \Exception
     */
    public function buildHostedCheckoutSpecificInput()
    {
        $hostedCheckoutSpecificInput = new HostedCheckoutSpecificInput();
        if ($this->settings->paymentMethodsSettings->redirectTemplateFilename) {
            $hostedCheckoutSpecificInput->setVariant($this->settings->paymentMethodsSettings->redirectTemplateFilename);
        }
        $cartIsoLang = Language::getIsoById($this->context->cart->id_lang);
        $hostedCheckoutSpecificInput->setLocale(str_replace('-', '_', Language::getLocaleByIso($cartIsoLang)));
        $hostedCheckoutSpecificInput->setReturnUrl(
            $this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectReturnHosted'])
        );
        if (false !== $this->idProduct) {
            $productFilter = new PaymentProductFilter();
            $productFilter->setProducts([(int) $this->idProduct]);
            $productFilterHostedCheckout = new PaymentProductFiltersHostedCheckout();
            $productFilterHostedCheckout->setRestrictTo($productFilter);
            $hostedCheckoutSpecificInput->setPaymentProductFilters($productFilterHostedCheckout);
        }
        if (false !== $this->tokenValue) {
            $hostedCheckoutSpecificInput->setTokens($this->tokenValue);
        }

        return $hostedCheckoutSpecificInput;
    }

    /**
     * @return CardPaymentMethodSpecificInput|false
     */
    public function buildCardPaymentMethodSpecificInput()
    {
        if (false !== $this->idProduct) {
            $product = $this->settings->paymentMethodsSettings->findRedirectPMByProductId((int) $this->idProduct);
            if (false === $product || PaymentMethodsSettings::PAYMENT_METHOD_CARD !== $product->type) {
                return false;
            }
        }

        $cardPaymentMethodSpecificInput = new CardPaymentMethodSpecificInput();
        $cardPaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );
        if (false !== $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setToken($this->tokenValue);
        }
        $cardPaymentMethodSpecificInput->setReturnUrl(
            $this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectReturnHosted'])
        );
        if (false !== $this->idProduct) {
            $cardPaymentMethodSpecificInput->setPaymentProductId($this->idProduct);
        }
        if (self::PRODUCT_ID_MAESTRO == $this->idProduct || true === $this->settings->advancedSettings->force3DsV2) {
            $threeDSecure = new ThreeDSecure();
            $threeDSecure->setChallengeIndicator('challenge-required');
            $cardPaymentMethodSpecificInput->setThreeDSecure($threeDSecure);
        }

        return $cardPaymentMethodSpecificInput;
    }

    /**
     * @return MobilePaymentMethodHostedCheckoutSpecificInput|false
     */
    public function buildMobilePaymentMethodSpecificInput()
    {
        if (false !== $this->idProduct) {
            $product = $this->settings->paymentMethodsSettings->findRedirectPMByProductId((int) $this->idProduct);
            if (false === $product || PaymentMethodsSettings::PAYMENT_METHOD_MOBILE !== $product->type) {
                return false;
            }
        }

        $mobilePaymentMethodSpecificInput = new MobilePaymentMethodHostedCheckoutSpecificInput();
        if (false !== $this->idProduct) {
            $mobilePaymentMethodSpecificInput->setPaymentProductId((int) $this->idProduct);
        }
        $mobilePaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );

        return $mobilePaymentMethodSpecificInput;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    public function buildOrder()
    {
        /** @var Order $order */
        $order = parent::buildOrder();
        $factory = new Factory();
        $generator = $factory->getGenerator(new Strength(Strength::LOW));
        $orderReferences = new OrderReferences();
        $orderReferences->setMerchantReference(
            $this->context->cart->id.'-'.$generator->generateString(7, self::REFERENCE_CHARS)
        );
        $order->setReferences($orderReferences);

        return $order;
    }
}
