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
 * Class AdvancedSettings
 */
class AdvancedSettings
{
    /** @var bool */
    public $advancedSettingsEnabled;

    /** @var bool|null */
    public $paymentFlowSettingsDisplayed;

    /** @var bool */
    public $force3DsV2;

    /** @var bool */
    public $switchEndpoint;

    /** @var string */
    public $testEndpoint;

    /** @var string */
    public $prodEndpoint;

    /** @var bool */
    public $logsEnabled;

    /** @var PaymentSettings */
    public $paymentSettings;

    /** @var bool */
    public $groupCardPaymentOptions;

    /** @var bool */
    public $omitOrderItemDetails;

    /** @var bool */
    public $threeDSExempted;

    /** @var bool */
    public $enforce3DS;

    /** @var string */
    public $threeDSExemptedType;

    /** @var string */
    public $threeDSExemptedValue;

    /** @var bool */
    public $surchargingEnabled;

    /** @var bool */
    public $displayWhatsNew;
}
