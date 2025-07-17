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

use Configuration;
use Context;
use Language;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CalculateSurchargeRequest;
use OnlinePayments\Sdk\Domain\CalculateSurchargeResponse;
use OnlinePayments\Sdk\Domain\CardSource;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use OnlinePayments\Sdk\ValidationException;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Worldlineop;
use WorldlineOP\PrestaShop\Builder\HostedPaymentRequestBuilder;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Repository\TokenRepository;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class PaymentOptionsPresenter
 */
class PaymentOptionsPresenter implements PresenterInterface
{
    const NO_SURCHARGE = 'NO_SURCHARGE';
    const MEALVOUCHER_PRODUCT_ID = 5402;

    /** @var Settings */
    private $settings;

    /** @var Worldlineop */
    private $module;

    /** @var Context */
    private $context;

    /**
     * ModuleConfigurationPresenter constructor.
     *
     * @param Worldlineop $module
     * @param Settings $settings
     * @param Context $context
     */
    public function __construct(Worldlineop $module, Settings $settings, Context $context)
    {
        $this->module = $module;
        $this->settings = $settings;
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function present()
    {
        $tokenPaymentOptions = [];
        $iframePaymentOption = [];
        $genericPaymentOption = [];
        $redirectPaymentMethodsOptions = [];
        try {
            $tokenPaymentOptions = $this->getTokenPaymentOptions();
        } catch (\Exception $e) {
            $this->module->logger->error('Error while presenting tokens payment option', ['message' => $e->getMessage()]);
        }
        try {
            $iframePaymentOption = $this->getIframePaymentOption();
        } catch (\Exception $e) {
            $this->module->logger->error('Error while presenting iframe payment option', ['message' => $e->getMessage()]);
        }
        try {
            $genericPaymentOption = $this->getGenericPaymentOption();
        } catch (\Exception $e) {
            $this->module->logger->error('Error while presenting generic payment option', ['message' => $e->getMessage()]);
        }
        try {
            $redirectPaymentMethodsOptions = $this->getRedirectPaymentMethodsOptions();
        } catch (\Exception $e) {
            $this->module->logger->error('Error while redirect redirect payment options', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'line' => $e->getLine()]);
        }

        return array_merge(
            $tokenPaymentOptions,
            $iframePaymentOption,
            $genericPaymentOption,
            $redirectPaymentMethodsOptions
        );
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    private function getTokenPaymentOptions()
    {
        $paymentMethodsSettings = $this->settings->paymentMethodsSettings;
        /** @var TokenRepository $tokenRepository */
        $tokenRepository = $this->module->getService('worldlineop.repository.token');
        /** @var \WorldlineopToken[] $tokens */
        $tokens = $tokenRepository->findByIdCustomerIdShop(
            $this->context->customer->id,
            $this->context->customer->secure_key,
            $this->context->shop->id
        );
        if (!$tokens) {
            return [];
        }
        $tokenOptions = [];
        if (false === $paymentMethodsSettings->displayIframePaymentOptions) {
            foreach ($tokens as $token) {
                $logoPath = realpath($this->module->getLocalPath() . sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                $paymentOption = new PaymentOption();
                //@formatter:off
                $paymentOption
                    ->setAction($this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectExternal', 'ajax' => true, 'productId' => $token->product_id, 'tokenId' => $token->id]))
                    ->setCallToActionText(sprintf($this->module->l('Pay with my previously saved card %s', 'PaymentOptionsPresenter'), $token->card_number));
                //@formatter:on
                if (false !== realpath($logoPath)) {
                    $paymentOption->setLogo($this->module->getPathUri() . sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                }

                $tokenOptions[] = $paymentOption;
            }
        } else {
            $tokenIds = [];
            /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
            $merchantClient = $this->module->getService('worldlineop.sdk.client');
            $cartIsoLang = Language::getIsoById($this->context->cart->id_lang);
            foreach ($tokens as $token) {
                $hostedTokenizationRequest = new CreateHostedTokenizationRequest();
                $hostedTokenizationRequest->setAskConsumerConsent(true);
                $hostedTokenizationRequest->setLocale(Language::getLocaleByIso($cartIsoLang));
                $hostedTokenizationRequest->setVariant($paymentMethodsSettings->iframeTemplateFilename);
                $hostedTokenizationRequest->setTokens($token->value);
                try {
                    $hostedTokenizationResponse = $merchantClient->hostedTokenization()
                        ->createHostedTokenization($hostedTokenizationRequest);
                } catch (\Exception $e) {
                    $this->module->logger->error($e->getMessage(), ['token_id' => $token->id, 'token_value' => $token->value]);
                    continue;
                }
                $this->module->logger->debug('HostedTokenizationResponse', ['json' => json_decode($hostedTokenizationResponse->toJson(), true)]);
                if (!empty($hostedTokenizationResponse->getInvalidTokens())) {
                    continue;
                }
                $tokenSurcharge = [];
                if ($this->settings->advancedSettings->surchargingEnabled) {
                    $surchargeRequest = new CalculateSurchargeRequest();
                    $amountOfMoney = new AmountOfMoney();
                    $amountOfMoney->setCurrencyCode(Tools::getIsoCurrencyCodeById($this->context->cart->id_currency));
                    $amountOfMoney->setAmount(
                        Tools::getRoundedAmountInCents(
                            $this->context->cart->getOrderTotal(),
                            Tools::getIsoCurrencyCodeById($this->context->cart->id_currency)
                        )
                    );
                    $card = new CardSource();
                    $card->setToken($token->value);
                    $surchargeRequest->setCardSource($card);
                    $surchargeRequest->setAmountOfMoney($amountOfMoney);
                    try {
                        /** @var CalculateSurchargeResponse $surchargeResponse */
                        $surchargeResponse = $merchantClient->services()->surchargeCalculation($surchargeRequest);
                        $this->module->logger->debug('Surcharge resp.', ['resp' => json_decode($surchargeResponse->toJson(), true)]);
                        $surcharges = $surchargeResponse->getSurcharges();
                        if (null !== $surcharges && self::NO_SURCHARGE !== $surcharges[0]->getResult()) {
                            $surcharge = $surcharges[0];
                            $amountWithoutSurcharge = Tools::getRoundedAmountFromCents(
                                $surcharge->getNetAmount()->getAmount(),
                                $surcharge->getNetAmount()->getCurrencyCode()
                            );
                            $amountWithSurcharge = Tools::getRoundedAmountFromCents(
                                $surcharge->getTotalAmount()->getAmount(),
                                $surcharge->getTotalAmount()->getCurrencyCode()
                            );
                            $surchargeAmount = Tools::getRoundedAmountFromCents(
                                $surcharge->getSurchargeAmount()->getAmount(),
                                $surcharge->getSurchargeAmount()->getCurrencyCode()
                            );
                            $tokenSurcharge = [
                                'amountWithoutSurcharge' => $amountWithoutSurcharge,
                                'amountWithSurcharge' => $amountWithSurcharge,
                                'surchargeAmount' => $surchargeAmount,
                                'currencyIso' => $surcharge->getNetAmount()->getCurrencyCode(),
                            ];
                        }
                    } catch (ValidationException $e) {
                        $this->module->logger->error($e->getMessage(), ['response' => json_decode($e->getResponse()->toJson(), true), 'token' => json_decode($surchargeRequest->toJson(), true)]);
                    } catch (\Exception $e) {
                        $this->module->logger->error($e->getMessage(), ['token' => json_decode($surchargeRequest->toJson(), true)]);
                    }
                }

                $redirectUrl = Settings::DEFAULT_SUBDOMAIN . $hostedTokenizationResponse->getPartialRedirectUrl();
                $createPaymentUrl = $this->context->link->getModuleLink($this->module->name, 'payment');
                $this->context->smarty->assign([
                    'tokenId' => $token->id_worldlineop_token,
                    'tokenSurcharge' => $tokenSurcharge,
                    'hostedTokenizationPageUrl' => $redirectUrl,
                    'createPaymentUrl' => $createPaymentUrl,
                    'cardToken' => $token->value,
                    'totalCartCents' => Tools::getRoundedAmountInCents($this->context->cart->getOrderTotal(), Tools::getIsoCurrencyCodeById($this->context->cart->id_currency)),
                    'cartCurrencyCode' => Tools::getIsoCurrencyCodeById($this->context->cart->id_currency),
                    'worldlineopCustomerToken' => \Tools::getToken(),
                    'surchargeEnabled' => $this->settings->advancedSettings->surchargingEnabled,
                ]);

                $logoPath = realpath($this->module->getLocalPath() . sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                $paymentOption = new PaymentOption();
                //@formatter:off
                $paymentOption
                    ->setCallToActionText(sprintf($this->module->l('Pay with my previously saved card %s', 'PaymentOptionsPresenter'), $token->card_number))
                    ->setAdditionalInformation($this->context->smarty->fetch('module:worldlineop/views/templates/front/hostedTokenizationAdditionalInformation_1click.tpl'))
                    ->setBinary(true)
                    ->setModuleName('worldlineop-token-htp-' . $token->id_worldlineop_token);
                //@formatter:on
                if (false !== realpath($logoPath)) {
                    $paymentOption->setLogo($this->module->getPathUri() . sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                }
                $tokenIds[] = ['id' => $token->id_worldlineop_token];
                $tokenOptions[] = $paymentOption;
            }
            if (!empty($tokenIds)) {
                $this->context->smarty->assign('tokenHTP', $tokenIds);
            }
        }

        return $tokenOptions;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getIframePaymentOption()
    {
        $paymentMethodsSettings = $this->settings->paymentMethodsSettings;
        if (false === $paymentMethodsSettings->displayIframePaymentOptions) {
            return [];
        }
        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        $cartIsoLang = Language::getIsoById($this->context->cart->id_lang);
        $hostedTokenizationRequest = new CreateHostedTokenizationRequest();
        $hostedTokenizationRequest->setAskConsumerConsent(true);
        $hostedTokenizationRequest->setLocale(str_replace('-', '_', Language::getLocaleByIso($cartIsoLang)));
        $hostedTokenizationRequest->setVariant($paymentMethodsSettings->iframeTemplateFilename);
        try {
            $hostedTokenizationResponse = $merchantClient->hostedTokenization()
                ->createHostedTokenization($hostedTokenizationRequest);
        } catch (\Exception $e) {
            $this->module->logger->error($e->getMessage(), ['json' => json_decode($hostedTokenizationRequest->toJson(), true)]);

            return [];
        }
        $redirectUrl = Settings::DEFAULT_SUBDOMAIN . $hostedTokenizationResponse->getPartialRedirectUrl();
        $createPaymentUrl = $this->context->link->getModuleLink($this->module->name, 'payment');
        $this->context->smarty->assign([
            'displayHTP' => true,
            'hostedTokenizationPageUrl' => $redirectUrl,
            'createPaymentUrl' => $createPaymentUrl,
            'totalCartCents' => Tools::getRoundedAmountInCents($this->context->cart->getOrderTotal(), Tools::getIsoCurrencyCodeById($this->context->cart->id_currency)),
            'cartCurrencyCode' => Tools::getIsoCurrencyCodeById($this->context->cart->id_currency),
            'worldlineopCustomerToken' => \Tools::getToken(),
            'surchargeEnabled' => $this->settings->advancedSettings->surchargingEnabled,
        ]);

        $defaultIsoLang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        $cta = $paymentMethodsSettings->iframeCallToAction;
        $paymentOption = new PaymentOption();
        $paymentOption
            ->setCallToActionText(isset($cta[$cartIsoLang]) ? $cta[$cartIsoLang] : $cta[$defaultIsoLang])
            ->setAdditionalInformation($this->context->smarty->fetch('module:worldlineop/views/templates/front/hostedTokenizationAdditionalInformation.tpl'))
            ->setBinary(true)
            ->setLogo($this->module->getPathUri() . 'views/img/payment_logos/' . $this->settings->paymentMethodsSettings->iframeLogoFilename)
            ->setModuleName('worldlineop-htp');

        return [$paymentOption];
    }

    /**
     * @return array
     */
    private function getGenericPaymentOption()
    {
        if (true === $this->settings->paymentMethodsSettings->displayGenericOption) {
            $cartIsoLang = Language::getIsoById($this->context->cart->id_lang);
            $defaultIsoLang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
            $cta = $this->settings->paymentMethodsSettings->redirectCallToAction;
            if ($this->settings->paymentMethodsSettings->genericLogoFilename) {
                $logo = sprintf(
                    $this->module->getPathUri() . 'views/img/payment_logos/%s',
                    $this->settings->paymentMethodsSettings->genericLogoFilename
                );
            } else {
                $logo = $this->module->getPathUri() . 'views/img/payment_logos/worldlineop_symbol.svg';
            }
            $paymentOption = new PaymentOption();
            $paymentOption
                ->setAction($this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectExternal', 'ajax' => true]))
                ->setLogo($logo)
                ->setCallToActionText(isset($cta[$cartIsoLang]) ? $cta[$cartIsoLang] : $cta[$defaultIsoLang]);

            return [$paymentOption];
        }

        return [];
    }

    /**
     * @return array
     */
    private function getRedirectPaymentMethodsOptions()
    {
        if (false === $this->settings->paymentMethodsSettings->displayRedirectPaymentOptions) {
            return [];
        }
        $paymentOptions = [];
        foreach ($this->settings->paymentMethodsSettings->redirectPaymentMethods as $paymentMethod) {
            if (false === $paymentMethod->enabled) {
                continue;
            }
            $paymentOption = new PaymentOption();
            //@formatter:off
            $paymentOption
                ->setAction($this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectExternal', 'ajax' => true, 'productId' => $paymentMethod->productId]))
                ->setLogo(sprintf($this->module->getPathUri() . 'views/img/payment_logos/%s.svg', $paymentMethod->productId))
                ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $paymentMethod->identifier));
            //@formatter:off

            $productId = $this->extractProductIdFromPaymentOption($paymentOption);
            if ((int)$productId === self::MEALVOUCHER_PRODUCT_ID &&
                (!$this->isEligibleForMealVoucher() || !$this->isCustomerDataValid())) {
                continue;
            }

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    /**
     * @return bool
     */
    private function isCustomerDataValid() {
        return $this->context->customer->id && $this->context->customer->email;
    }

    /**
     * @param PaymentOption $paymentOption
     * @return mixed|null
     */
    private function extractProductIdFromPaymentOption(PaymentOption $paymentOption) {
        $urlParts = parse_url($paymentOption->getAction());
        parse_str($urlParts['query'], $queryParams);

        return isset($queryParams['productId']) ? $queryParams['productId'] : null;
    }

    /**
     * @return bool
     */
    private function isEligibleForMealVoucher()
    {
        foreach ($this->context->cart->getProducts() as $product) {
            $productType = Tools::getGiftCardTypeByIdProduct($product['id_product']);
            if (in_array($productType, $this->getEligibleProductTypes())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return eligible product types for meal vouchers
     *
     * @return string[]
     */
    private function getEligibleProductTypes()
    {
        return array(
            HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_FOOD_DRINK,
            HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_HOME_GARDEN,
            HostedPaymentRequestBuilder::GIFT_CARD_PRODUCT_TYPE_GIFT_FLOWERS
        );
    }
}
