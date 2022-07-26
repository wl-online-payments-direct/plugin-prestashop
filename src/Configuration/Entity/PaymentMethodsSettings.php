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

namespace WorldlineOP\PrestaShop\Configuration\Entity;

/**
 * Class PaymentMethodsSettings
 * @package WorldlineOP\PrestaShop\Configuration\Entity
 */
class PaymentMethodsSettings
{
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_REDIRECT = 'redirect';
    const PAYMENT_METHOD_MOBILE = 'mobile';

    /** @var bool $displayGenericOption */
    public $displayGenericOption;

    /** @var string $genericLogoFilename */
    public $genericLogoFilename;

    /** @var string $redirectTemplateFilename */
    public $redirectTemplateFilename;

    /** @var string $iframeTemplateFilename */
    public $iframeTemplateFilename;

    /** @var string[] $redirectCallToAction */
    public $redirectCallToAction;

    /** @var string[] $iframeCallToAction */
    public $iframeCallToAction;

    /** @var string $iframeLogo */
    public $iframeLogoFilename;

    /** @var bool $displayRedirectPaymentOptions */
    public $displayRedirectPaymentOptions;

    /** @var bool $displayIframePaymentOptions */
    public $displayIframePaymentOptions;

    /** @var PaymentMethod[] $redirectPaymentMethods */
    public $redirectPaymentMethods;

    /** @var PaymentMethod[] $iframePaymentMethods */
    public $iframePaymentMethods;

    /**
     * @param int $productId
     * @return bool|PaymentMethod
     */
    public function findRedirectPMByProductId($productId)
    {
        foreach ($this->redirectPaymentMethods as $paymentMethod) {
            if ($paymentMethod->productId === $productId) {
                return $paymentMethod;
            }
        }

        return false;
    }

    /**
     * @param int $productId
     * @return bool|PaymentMethod
     */
    public function findIframePMByProductId($productId)
    {
        foreach ($this->iframePaymentMethods as $paymentMethod) {
            if ($paymentMethod->productId === $productId) {
                return $paymentMethod;
            }
        }

        return false;
    }
}
