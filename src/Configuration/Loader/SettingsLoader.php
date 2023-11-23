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

namespace WorldlineOP\PrestaShop\Configuration\Loader;

use Symfony\Component\Serializer\Serializer;
use WorldlineOP\PrestaShop\Configuration\Entity\AccountSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\AdvancedSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethodsSettings;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;

/**
 * Class SettingsLoader
 */
class SettingsLoader
{
    /** @var Serializer */
    private $serializer;

    /** @var int|null */
    private $idShop;

    /** @var int|null */
    private $idShopGroup;

    /**
     * SettingsLoader constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->idShop = null;
        $this->idShopGroup = null;
    }

    /**
     * @return Settings
     */
    public function deserialize()
    {
        $jsonAccount = \Configuration::get('WORLDLINEOP_ACCOUNT_SETTINGS', null, $this->idShopGroup, $this->idShop) ?: '[]';
        $jsonAdvancedSettings = \Configuration::get('WORLDLINEOP_ADVANCED_SETTINGS', null, $this->idShopGroup, $this->idShop) ?: '[]';
        $jsonPaymentMethodsSettings = \Configuration::get('WORLDLINEOP_PAYMENT_METHODS_SETTINGS', null, $this->idShopGroup, $this->idShop) ?: '[]';

        $accountSettings = $this->serializer->deserialize($jsonAccount, AccountSettings::class, 'json');
        $advancedSettings = $this->serializer->deserialize($jsonAdvancedSettings, AdvancedSettings::class, 'json');
        $paymentMethodsSettings = $this->serializer->deserialize($jsonPaymentMethodsSettings, PaymentMethodsSettings::class, 'json');

        $settings = new Settings();
        $settings->accountSettings = $accountSettings;
        $settings->advancedSettings = $advancedSettings;
        $settings->paymentMethodsSettings = $paymentMethodsSettings;

        return $settings->postLoading();
    }

    /**
     * @return array
     */
    public function normalize()
    {
        $settings = $this->deserialize();

        return $this->serializer->normalize($settings);
    }

    /**
     * @param int|null $idShop
     * @param int|null $idShopGroup
     *
     * @return Settings
     */
    public function setContext($idShop = null, $idShopGroup = null)
    {
        $this->idShop = (int) $idShop;
        $this->idShopGroup = (int) $idShopGroup;

        return $this->deserialize();
    }
}
