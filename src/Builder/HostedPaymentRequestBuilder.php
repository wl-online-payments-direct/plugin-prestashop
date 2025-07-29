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

namespace WorldlineOP\PrestaShop\Builder;

use Language;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputForHostedCheckout;
use OnlinePayments\Sdk\Domain\GPayThreeDSecure;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\LineItem;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodHostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\MobilePaymentProduct320SpecificInput;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderLineDetails;
use OnlinePayments\Sdk\Domain\OrderReferences;
use OnlinePayments\Sdk\Domain\PaymentProduct130SpecificInput;
use OnlinePayments\Sdk\Domain\PaymentProduct130SpecificThreeDSecure;
use OnlinePayments\Sdk\Domain\PaymentProductFilter;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckout;
use OnlinePayments\Sdk\Domain\RedirectionData;
use OnlinePayments\Sdk\Domain\ShoppingCart;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use RandomLib\Factory;
use SecurityLib\Strength;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethodsSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentSettings;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class HostedPaymentRequestBuilder
 */
class HostedPaymentRequestBuilder extends AbstractRequestBuilder
{
    const GIFT_CARD_PRODUCT_TYPE_FOOD_DRINK = 'FoodAndDrink';
    const GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN = 'HomeAndGarden';
    const GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS = 'GiftAndFlowers';
    const GIFT_CARD_PRODUCT_TYPE_NONE = 'none';
    const MEALVOUCHER_PRODUCT_ID = 5402;

    /**
     * @return HostedCheckoutSpecificInput|null
     *
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
        if ($this->idProduct) {
            $productFilter = new PaymentProductFilter();
            $productFilter->setProducts([(int)$this->idProduct]);
            $productFilterHostedCheckout = new PaymentProductFiltersHostedCheckout();
            $productFilterHostedCheckout->setRestrictTo($productFilter);

            // exclude meal vouchers
            $excludeMealVoucherFilter = new PaymentProductFilter();
            $excludeMealVoucherFilter->setProducts([self::MEALVOUCHER_PRODUCT_ID]);
            $productFilterHostedCheckout->setExclude($excludeMealVoucherFilter);

            $hostedCheckoutSpecificInput->setPaymentProductFilters($productFilterHostedCheckout);
        }
        if (false !== $this->tokenValue) {
            $hostedCheckoutSpecificInput->setTokens($this->tokenValue);
        }
        if (true === $this->settings->advancedSettings->groupCardPaymentOptions) {
            $cardPaymentMethodSpecificInputForHC = new CardPaymentMethodSpecificInputForHostedCheckout();
            $cardPaymentMethodSpecificInputForHC->setGroupCards(true);
            $hostedCheckoutSpecificInput->setCardPaymentMethodSpecificInput($cardPaymentMethodSpecificInputForHC);
        }

        return $hostedCheckoutSpecificInput;
    }

    /**
     * @return CardPaymentMethodSpecificInput|false
     *
     * @throws \Exception
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
        if (self::PRODUCT_ID_INTERSOLVE == $this->idProduct) {
            $cardPaymentMethodSpecificInput->setAuthorizationMode(PaymentSettings::TRANSACTION_TYPE_IMMEDIATE);
        } else {
            $cardPaymentMethodSpecificInput->setAuthorizationMode(
                $this->settings->advancedSettings->paymentSettings->transactionType
            );
        }
        if (false !== $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setToken($this->tokenValue);
        }
        $cardPaymentMethodSpecificInput->setReturnUrl(
            $this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectReturnHosted'])
        );
        if (false !== $this->idProduct) {
            $cardPaymentMethodSpecificInput->setPaymentProductId($this->idProduct);
        }
        $threeDSecure = new ThreeDSecure();
        $threeDSecure->setSkipAuthentication(true);
        if (self::PRODUCT_ID_MAESTRO == $this->idProduct || true === $this->settings->advancedSettings->force3DsV2) {
            $threeDSecure->setSkipAuthentication(false);
        }
        $orderTotalInEuros = Tools::getAmountInEuros($this->context->cart->getOrderTotal(), new \Currency($this->context->cart->id_currency));
        $threeDSExemptedValue = $this->settings->advancedSettings->threeDSExemptedValue;
        $threeDSExemptedType = $this->settings->advancedSettings->threeDSExemptedType;
        if (true === $this->settings->advancedSettings->threeDSExempted && $threeDSExemptedValue >= $orderTotalInEuros) {
            $threeDSecure->setSkipAuthentication(true);
            $threeDSecure->setExemptionRequest($threeDSExemptedType);
            $threeDSecure->setSkipSoftDecline(false);
        }
        if (true === $this->settings->advancedSettings->enforce3DS) {
            $threeDSecure->setChallengeIndicator(self::CHALLENGE_INDICATOR_REQUIRED);
        }
        $cardPaymentMethodSpecificInput->setThreeDSecure($threeDSecure);

        $shoppingCartPresented = $this->shoppingCartPresenter->present($this->context->cart);
        $numberOfItems = min(count($shoppingCartPresented['products']), self::MAX_NUMBER_OF_ITEMS);

        if (true === $this->settings->advancedSettings->force3DsV2) {
            $paymentProduct130SpecificInput = new PaymentProduct130SpecificInput();
            $paymentProduct130ThreeDSecure = new PaymentProduct130SpecificThreeDSecure();
            $paymentProduct130ThreeDSecure->setUsecase('single-amount');
            $paymentProduct130ThreeDSecure->setNumberOfItems($numberOfItems);

            if (!$this->settings->advancedSettings->threeDSExempted) {
                $paymentProduct130ThreeDSecure->setAcquirerExemption(false);
            } elseif ($this->settings->advancedSettings->threeDSExempted) {
                $this->settings->advancedSettings->threeDSExemptedValue >= $orderTotalInEuros ?
                    $paymentProduct130ThreeDSecure->setAcquirerExemption(true) :
                    $paymentProduct130ThreeDSecure->setAcquirerExemption(false);
            }
            $paymentProduct130SpecificInput->setThreeDSecure($paymentProduct130ThreeDSecure);
            $cardPaymentMethodSpecificInput->setPaymentProduct130SpecificInput($paymentProduct130SpecificInput);
        }

        if (false === $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor(self::CARD_ON_FILE_REQUESTOR_FIRST);
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator(self::CARD_ON_FILE_SEQUENCE_INDICATOR_FIRST);
        } else {
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor(self::CARD_ON_FILE_REQUESTOR_SUBSEQUENT);
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator(self::CARD_ON_FILE_SEQUENCE_INDICATOR_SUBSEQUENT);
        }

        return $cardPaymentMethodSpecificInput;
    }

    /**
     * @return MobilePaymentMethodHostedCheckoutSpecificInput|false
     * @throws \Exception
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
            $mobilePaymentMethodSpecificInput->setPaymentProductId((int)$this->idProduct);
        }
        $mobilePaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );

        $paymentProduct320SpecificInput = new MobilePaymentProduct320SpecificInput();
        $gPayThreeDSecure = new GPayThreeDSecure();

        if (!$this->settings->advancedSettings->force3DsV2) {
            $gPayThreeDSecure->setSkipAuthentication(true);
        } else {
            if (!$this->settings->advancedSettings->enforce3DS && !$this->settings->advancedSettings->threeDSExempted) {
                $gPayThreeDSecure->setChallengeIndicator(self::CHALLENGE_INDICATOR_NO_PREFERENCE);
                $gPayThreeDSecure->setSkipAuthentication(false);
            } elseif ($this->settings->advancedSettings->enforce3DS) {
                $gPayThreeDSecure->setChallengeIndicator(self::CHALLENGE_INDICATOR_REQUIRED);
                $gPayThreeDSecure->setSkipAuthentication(false);
            } elseif ($this->settings->advancedSettings->threeDSExempted) {
                $threeDSExemptionType = $this->settings->advancedSettings->threeDSExemptedType;
                $threeDSExemptionValue = $this->settings->advancedSettings->threeDSExemptedValue;
                $orderTotalInEuros = Tools::getAmountInEuros($this->context->cart->getOrderTotal(),
                    new \Currency($this->context->cart->id_currency));
                if ($threeDSExemptionValue >= $orderTotalInEuros) {
                    $gPayThreeDSecure->setSkipAuthentication(true);
                    $gPayThreeDSecure->setExemptionRequest($threeDSExemptionType);
                } else {
                    $gPayThreeDSecure->setSkipAuthentication(false);
                }
            }
            $gPayRedirectionData = new RedirectionData();
            $gPayRedirectionData->setReturnUrl($this->context->link->getModuleLink(
                $this->module->name, 'redirect', ['action' => 'redirectReturnHosted']));
            $gPayThreeDSecure->setRedirectionData($gPayRedirectionData);
        }

        $paymentProduct320SpecificInput->setThreeDSecure($gPayThreeDSecure);
        $mobilePaymentMethodSpecificInput->setPaymentProduct320SpecificInput($paymentProduct320SpecificInput);


        return $mobilePaymentMethodSpecificInput;
    }

    /**
     * @return Order
     *
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
            $this->context->cart->id . '-' . $generator->generateString(7, self::REFERENCE_CHARS)
        );
        $order->setReferences($orderReferences);
        try {
            $productId = (int)$this->idProduct === self::MEALVOUCHER_PRODUCT_ID ? self::MEALVOUCHER_PRODUCT_ID : null;
            $shoppingCartPresented = $this->shoppingCartPresenter->present($this->context->cart, $productId);
        } catch (\Exception $e) {
            return $order;
        }
        $shoppingCart = new ShoppingCart();

        $items = [];
        foreach ($shoppingCartPresented['products'] as $product) {
            $item = new LineItem();
            $itemAmount = new AmountOfMoney();
            $amount = (int) (string) $product['totalWithTax'];
            if ((int)$this->idProduct === self::MEALVOUCHER_PRODUCT_ID) {
                // adding shipping price to the product price for meal vouchers
                $amount += (int) (string) $shoppingCartPresented['shipping']['priceWithTax'];
            }
            $itemAmount->setAmount($amount);
            $itemAmount->setCurrencyCode(Tools::getIsoCurrencyCodeById($shoppingCartPresented['cart']->id_currency));
            $item->setAmountOfMoney($itemAmount);
            $itemLineDetails = new OrderLineDetails();
            $price = (int) (string) $product['productPrice'];
            if ((int)$this->idProduct === self::MEALVOUCHER_PRODUCT_ID) {
                // adding shipping price to the product price for meal vouchers
                $price += (int) (string) $shoppingCartPresented['shipping']['priceWithTax'];
            }
            $itemLineDetails->setProductPrice($price);
            $itemLineDetails->setDiscountAmount((int) (string) $product['discountPrice']);
            $itemLineDetails->setProductCode($product['productCode']);
            $itemLineDetails->setProductName($product['productName']);
            $itemLineDetails->setProductType($product['productType']);
            $itemLineDetails->setQuantity($product['quantity']);
            $itemLineDetails->setTaxAmount((int) (string) $product['tax']);
            $itemLineDetails->setUnit('piece');
            $item->setOrderLineDetails($itemLineDetails);
            $items[] = $item;
        }
        if ((int)$this->idProduct !== self::MEALVOUCHER_PRODUCT_ID) {
            $shippingItem = new LineItem();
            $shippingItemAmount = new AmountOfMoney();
            $shippingItemAmount->setAmount((int) (string) $shoppingCartPresented['shipping']['priceWithTax']);
            $shippingItemAmount->setCurrencyCode(Tools::getIsoCurrencyCodeById($shoppingCartPresented['cart']->id_currency));
            $shippingItem->setAmountOfMoney($shippingItemAmount);
            $shippingItemLineDetails = new OrderLineDetails();
            $shippingItemLineDetails->setProductPrice((int) (string) $shoppingCartPresented['shipping']['priceWithoutTax']);
            $shippingItemLineDetails->setDiscountAmount((int) (string) $shoppingCartPresented['shipping']['discountPrice']);
            $shippingItemLineDetails->setProductCode('SHIPPING');
            $shippingItemLineDetails->setProductName($this->module->l('Shipping cost'));
            $shippingItemLineDetails->setQuantity(1);
            $shippingItemLineDetails->setTaxAmount((int) (string) $shoppingCartPresented['shipping']['tax']);
            $shippingItemLineDetails->setUnit('piece');
            $shippingItemLineDetails->setProductType($shoppingCartPresented['shipping']['type']);
            $shippingItem->setOrderLineDetails($shippingItemLineDetails);
            $items[] = $shippingItem;
        }
        $shoppingCart->setItems($items);
        if (!$this->settings->advancedSettings->omitOrderItemDetails) {
            $order->setShoppingCart($shoppingCart);
        }

        return $order;
    }
}
