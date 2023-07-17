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
 * Class AdvancedSettings
 * @package WorldlineOP\PrestaShop\Configuration\Entity
 */
class AdvancedSettings
{
    /** @var bool $advancedSettingsEnabled */
    public $advancedSettingsEnabled;

    /** @var bool|null $paymentFlowSettingsDisplayed */
    public $paymentFlowSettingsDisplayed;

    /** @var bool $force3DsV2 */
    public $force3DsV2;

    /** @var bool $switchEndpoint */
    public $switchEndpoint;

    /** @var string $testEndpoint */
    public $testEndpoint;

    /** @var string $prodEndpoint */
    public $prodEndpoint;

    /** @var bool $logsEnabled */
    public $logsEnabled;

    /** @var PaymentSettings $paymentSettings */
    public $paymentSettings;

    /** @var bool $groupCardPaymentOptions */
    public $groupCardPaymentOptions;

    /** @var bool $threeDSExempted */
    public $threeDSExempted;

    /** @var bool $enforce3DS */
    public $enforce3DS;

    /** @var bool $surchargingEnabled */
    public $surchargingEnabled;

    /** @var bool $displayWhatsNew */
    public $displayWhatsNew;
}
