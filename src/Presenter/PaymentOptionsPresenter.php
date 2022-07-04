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

use Configuration;
use Context;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use Language;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Worldlineop;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Repository\TokenRepository;
use WorldlineOP\PrestaShop\Utils\Decimal;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class PaymentOptionsPresenter
 * @package WorldlineOP\PrestaShop\Presenter
 */
class PaymentOptionsPresenter implements PresenterInterface
{
    /** @var Settings $settingsLoader */
    private $settings;

    /** @var Worldlineop $module */
    private $module;

    /** @var Context $context */
    private $context;

    /**
     * ModuleConfigurationPresenter constructor.
     * @param Worldlineop $module
     * @param Settings    $settings
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
            $this->module->logger->error('Error while redirect iframe payment options', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'line' => $e->getLine()]);
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
                $logoPath = realpath($this->module->getLocalPath().sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                $paymentOption = new PaymentOption();
                //@formatter:off
                $paymentOption
                    ->setAction($this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectExternal', 'ajax' => true, 'productId' => $token->product_id, 'tokenId' => $token->id]))
                    ->setCallToActionText(sprintf($this->module->l('Pay with my previously saved card %s', 'PaymentOptionsPresenter'), $token->card_number));
                //@formatter:on
                if (false !== realpath($logoPath)) {
                    $paymentOption->setLogo($this->module->getPathUri().sprintf('views/img/payment_logos/%s.svg', $token->product_id));
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

                $redirectUrl = Settings::DEFAULT_SUBDOMAIN.$hostedTokenizationResponse->getPartialRedirectUrl();
                $createPaymentUrl = $this->context->link->getModuleLink($this->module->name, 'payment');
                $this->context->smarty->assign([
                    'tokenId' => $token->id_worldlineop_token,
                    'hostedTokenizationPageUrl' => $redirectUrl,
                    'createPaymentUrl' => $createPaymentUrl,
                    'cardToken' => $token->value,
                    'totalCartCents' => Decimal::multiply((string) $this->context->cart->getOrderTotal(), '100')->getIntegerPart(),
                    'cartCurrencyCode' => Tools::getIsoCurrencyCodeById($this->context->cart->id_currency),
                    'worldlineopCustomerToken' => \Tools::getToken(),
                ]);

                $logoPath = realpath($this->module->getLocalPath().sprintf('views/img/payment_logos/%s.svg', $token->product_id));
                $paymentOption = new PaymentOption();
                //@formatter:off
                $paymentOption
                    ->setCallToActionText(sprintf($this->module->l('Pay with my previously saved card %s', 'PaymentOptionsPresenter'), $token->card_number))
                    ->setAdditionalInformation($this->context->smarty->fetch('module:worldlineop/views/templates/front/hostedTokenizationAdditionalInformation_1click.tpl'))
                    ->setBinary(true)
                    ->setModuleName('worldlineop-token-htp-'.$token->id_worldlineop_token);
                //@formatter:on
                if (false !== realpath($logoPath)) {
                    $paymentOption->setLogo($this->module->getPathUri().sprintf('views/img/payment_logos/%s.svg', $token->product_id));
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
        $redirectUrl = Settings::DEFAULT_SUBDOMAIN.$hostedTokenizationResponse->getPartialRedirectUrl();
        $createPaymentUrl = $this->context->link->getModuleLink($this->module->name, 'payment');
        $this->context->smarty->assign([
            'displayHTP' => true,
            'hostedTokenizationPageUrl' => $redirectUrl,
            'createPaymentUrl' => $createPaymentUrl,
            'totalCartCents' => Decimal::multiply((string) $this->context->cart->getOrderTotal(), '100')->getIntegerPart(),
            'cartCurrencyCode' => Tools::getIsoCurrencyCodeById($this->context->cart->id_currency),
            'worldlineopCustomerToken' => \Tools::getToken(),
        ]);

        $defaultIsoLang = Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'));
        $cta = $paymentMethodsSettings->iframeCallToAction;
        $paymentOption = new PaymentOption();
        $paymentOption
            ->setCallToActionText(isset($cta[$cartIsoLang]) ? $cta[$cartIsoLang] : $cta[$defaultIsoLang])
            ->setAdditionalInformation($this->context->smarty->fetch('module:worldlineop/views/templates/front/hostedTokenizationAdditionalInformation.tpl'))
            ->setBinary(true)
            ->setLogo($this->module->getPathUri().'views/img/payment_logos/'.$this->settings->paymentMethodsSettings->iframeLogoFilename)
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
            if ($this->settings->advancedSettings->switchEndpoint && $this->settings->advancedSettings->endpointLogoFilename) {
                $logo = sprintf(
                    $this->module->getPathUri().'views/img/payment_logos/%s',
                    $this->settings->advancedSettings->endpointLogoFilename
                );
            } else {
                $logo = $this->module->getPathUri().'views/img/payment_logos/worldlineop_symbol.svg';
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
                ->setLogo(sprintf($this->module->getPathUri().'views/img/payment_logos/%s.svg', $paymentMethod->productId))
                ->setCallToActionText(sprintf($this->module->l('Pay with %s', 'PaymentOptionsPresenter'), $paymentMethod->identifier));
            //@formatter:off

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }
}
