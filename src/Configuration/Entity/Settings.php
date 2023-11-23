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

namespace WorldlineOP\PrestaShop\Configuration\Entity;

/**
 * Class Settings
 */
class Settings
{
    const DEFAULT_SDK_ENDPOINT_TEST = 'https://payment.preprod.direct.worldline-solutions.com';
    const DEFAULT_SDK_ENDPOINT_PROD = 'https://payment.direct.worldline-solutions.com';

    const DEFAULT_SUBDOMAIN = 'https://payment.';

    /** @var AccountSettings */
    public $accountSettings;

    /** @var AdvancedSettings */
    public $advancedSettings;

    /** @var PaymentMethodsSettings */
    public $paymentMethodsSettings;

    /** @var \stdClass */
    public $credentials;

    /**
     * @return $this
     */
    public function postLoading()
    {
        $this->credentials = new \stdClass();
        if (AccountSettings::ACCOUNT_MODE_TEST === $this->accountSettings->environment) {
            $this->credentials->pspid = $this->accountSettings->testPspid;
            $this->credentials->apiKey = $this->accountSettings->testApiKey;
            $this->credentials->apiSecret = $this->accountSettings->testApiSecret;
            $this->credentials->webhooksKey = $this->accountSettings->testWebhooksKey;
            $this->credentials->webhooksSecret = $this->accountSettings->testWebhooksSecret;
            $endpoint = $this->advancedSettings->testEndpoint ?: self::DEFAULT_SDK_ENDPOINT_TEST;
        } else {
            $this->credentials->pspid = $this->accountSettings->prodPspid;
            $this->credentials->apiKey = $this->accountSettings->prodApiKey;
            $this->credentials->apiSecret = $this->accountSettings->prodApiSecret;
            $this->credentials->webhooksKey = $this->accountSettings->prodWebhooksKey;
            $this->credentials->webhooksSecret = $this->accountSettings->prodWebhooksSecret;
            $endpoint = $this->advancedSettings->prodEndpoint ?: self::DEFAULT_SDK_ENDPOINT_PROD;
        }
        $this->credentials->endpoint = rtrim($endpoint, '/');
        if (false === $this->advancedSettings->force3DsV2) {
            $this->advancedSettings->enforce3DS = false;
            $this->advancedSettings->threeDSExempted = false;
        } else {
            if (true === $this->advancedSettings->enforce3DS) {
                $this->advancedSettings->threeDSExempted = false;
            }
        }

        return $this;
    }
}
