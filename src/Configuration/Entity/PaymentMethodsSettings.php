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
 * Class PaymentMethodsSettings
 */
class PaymentMethodsSettings
{
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_REDIRECT = 'redirect';
    const PAYMENT_METHOD_MOBILE = 'mobile';

    /** @var bool */
    public $displayGenericOption;

    /** @var string */
    public $genericLogoFilename;

    /** @var string */
    public $redirectTemplateFilename;

    /** @var string */
    public $iframeTemplateFilename;

    /** @var string[] */
    public $redirectCallToAction;

    /** @var string[] */
    public $iframeCallToAction;

    /** @var string */
    public $iframeLogoFilename;

    /** @var bool */
    public $displayRedirectPaymentOptions;

    /** @var bool */
    public $displayIframePaymentOptions;

    /** @var PaymentMethod[] */
    public $redirectPaymentMethods;

    /** @var PaymentMethod[] */
    public $iframePaymentMethods;

    /**
     * @param int $productId
     *
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
     *
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
