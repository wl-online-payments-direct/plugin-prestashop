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

use Country;
use OnlinePayments\Sdk\Domain\AddressPersonal;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\LineItem;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodHostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderLineDetails;
use OnlinePayments\Sdk\Domain\OrderReferences;
use OnlinePayments\Sdk\Domain\PaymentProductFilter;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckout;
use OnlinePayments\Sdk\Domain\PersonalName;
use OnlinePayments\Sdk\Domain\Shipping;
use OnlinePayments\Sdk\Domain\ShoppingCart;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
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
        try {
            $shoppingCartPresented = $this->shoppingCartPresenter->present($this->context->cart);
        } catch (\Exception $e) {
            return $order;
        }
        $shoppingCart = new ShoppingCart();
        $items = [];
        foreach ($shoppingCartPresented['products'] as $product) {
            $item = new LineItem();
            $itemAmount = new AmountOfMoney();
            $itemAmount->setAmount((int)(string) $product['totalWithTax']);
            $itemAmount->setCurrencyCode('EUR');
            $item->setAmountOfMoney($itemAmount);
            $itemLineDetails = new OrderLineDetails();
            $itemLineDetails->setProductPrice((int)(string) $product['productPrice']);
            $itemLineDetails->setDiscountAmount((int)(string) $product['discountPrice']);
            $itemLineDetails->setProductCode($product['productCode']);
            $itemLineDetails->setProductName($product['productName']);
            $itemLineDetails->setQuantity($product['quantity']);
            $itemLineDetails->setTaxAmount((int)(string) $product['tax']);
            $itemLineDetails->setUnit('piece');
            $itemLineDetails->setProductType('');
            $item->setOrderLineDetails($itemLineDetails);
            $items[] = $item;
        }
        $shippingItem = new LineItem();
        $shippingItemAmount = new AmountOfMoney();
        $shippingItemAmount->setAmount((int)(string) $shoppingCartPresented['shipping']['priceWithTax']);
        $shippingItemAmount->setCurrencyCode('EUR');
        $shippingItem->setAmountOfMoney($shippingItemAmount);
        $shippingItemLineDetails = new OrderLineDetails();
        $shippingItemLineDetails->setProductPrice((int)(string) $shoppingCartPresented['shipping']['priceWithoutTax']);
        $shippingItemLineDetails->setDiscountAmount((int)(string) $shoppingCartPresented['shipping']['discountPrice']);
        $shippingItemLineDetails->setProductCode('SHIPPING');
        $shippingItemLineDetails->setProductName($this->module->l('Shipping cost'));
        $shippingItemLineDetails->setQuantity(1);
        $shippingItemLineDetails->setTaxAmount((int)(string) $shoppingCartPresented['shipping']['tax']);
        $shippingItemLineDetails->setUnit('piece');
        $shippingItemLineDetails->setProductType('');
        $shippingItem->setOrderLineDetails($shippingItemLineDetails);
        $items[] = $shippingItem;
        $shoppingCart->setItems($items);
        $order->setShoppingCart($shoppingCart);

        $shipping = new Shipping();
        $customerAddress = new \Address((int) $this->context->cart->id_address_delivery);
        $shippingAddress = new AddressPersonal();
        $shippingAddress->setCountryCode(Country::getIsoById($customerAddress->id_country));
        $shippingAddress->setCity($customerAddress->city);
        $shippingAddress->setStreet($customerAddress->address1);
        $shippingAddress->setAdditionalInfo($customerAddress->address2);
        $shippingAddress->setZip($customerAddress->postcode);
        if ($customerAddress->id_state) {
            $shippingAddress->setState(\State::getNameById($customerAddress->id_state));
        }
        $personalName = new PersonalName();
        $personalName->setFirstName($customerAddress->firstname);
        $personalName->setSurname($customerAddress->lastname);
        $shippingAddress->setName($personalName);
        $shipping->setAddress($shippingAddress);
        $shipping->setEmailAddress($this->context->customer->email);
        $shipping->setAddressIndicator($this->context->cart->id_address_delivery === $this->context->cart->id_address_invoice ? 'same-as-billing' : 'different-than-billing');
        $order->setShipping($shipping);

        return $order;
    }
}
