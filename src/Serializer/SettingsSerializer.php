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

namespace WorldlineOP\PrestaShop\Serializer;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class SettingsSerializer
 */
class SettingsSerializer
{
    /** @var Serializer */
    private $serializer;

    /**
     * SettingsSerializer constructor.
     */
    public function __construct()
    {
        $objectNormalizer = new ObjectNormalizer(null, null, null, new PhpDocExtractor());
        $objectNormalizer
            ->setCallbacks([
                'redirectPaymentMethods' => function ($value) {
                    return null === $value ? [] : $value;
                },
                'iframePaymentMethods' => function ($value) {
                    return null === $value ? [] : $value;
                },
            ]);
        $advancedSettingsDenormalizer = new AdvancedSettingsDenormalizer();
        $advancedSettingsDenormalizer->setDenormalizer($objectNormalizer);
        $paymentMethodsSettingsDenormalizer = new PaymentMethodsSettingsDenormalizer();
        $paymentMethodsSettingsDenormalizer->setDenormalizer($objectNormalizer);
        $settingsEncoder = new SettingsEncoder();

        $this->serializer = new Serializer([$advancedSettingsDenormalizer, $paymentMethodsSettingsDenormalizer, $objectNormalizer, new ArrayDenormalizer()], [$settingsEncoder]);
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
}
