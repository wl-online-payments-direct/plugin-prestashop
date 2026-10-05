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

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    /**
     * Whether customers may save a card and pay with a previously saved one.
     *
     * Defaults to TRUE in PHP, not merely in the form: settings are loaded by deserializing the
     * stored WORLDLINEOP_ADVANCED_SETTINGS JSON straight into this entity (SettingsLoader), and the
     * options resolver only runs when the form is saved. A shop that upgrades has no such key in its
     * stored JSON, so without this default the property would arrive null and every existing shop
     * would silently lose card saving.
     *
     * @var bool
     */
    public $enableSavingCards = true;

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
    public $displayPaymentConfirmationPage;

    /** @var bool */
    public $displayWhatsNew;
}
