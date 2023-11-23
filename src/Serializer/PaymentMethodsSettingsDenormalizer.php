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

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethod;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethodsSettings;

/**
 * Class PaymentMethodsSettingsDenormalizer
 */
class PaymentMethodsSettingsDenormalizer extends ObjectNormalizer
{
    use DenormalizerAwareTrait;

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param string $type The expected class to instantiate
     * @param string $format Format the given data was extracted from
     * @param array $context Options available to the denormalizer
     *
     * @return object|array
     *
     * @throws \Symfony\Component\Serializer\Exception\BadMethodCallException Occurs when the normalizer is not called in an expected context
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException Occurs when the arguments are not coherent or not supported
     * @throws \Symfony\Component\Serializer\Exception\UnexpectedValueException Occurs when the item cannot be hydrated with the given data
     * @throws \Symfony\Component\Serializer\Exception\ExtraAttributesException Occurs when the item doesn't have attribute to receive given data
     * @throws \Symfony\Component\Serializer\Exception\LogicException Occurs when the normalizer is not supposed to denormalize
     * @throws \Symfony\Component\Serializer\Exception\RuntimeException Occurs if the class cannot be instantiated
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface Occurs for all the other cases of errors
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $obj = parent::denormalize($data, $type, $format, $context);
        if (isset($data['redirectPaymentMethods'])) {
            $array = [];
            foreach ($data['redirectPaymentMethods'] as $redirectPaymentMethod) {
                $array[] = $this->denormalizer->denormalize(
                    $redirectPaymentMethod,
                    PaymentMethod::class,
                    $format,
                    $context
                );
            }
            $obj->redirectPaymentMethods = $array;
        }
        if (isset($data['iframePaymentMethods'])) {
            $array = [];
            foreach ($data['iframePaymentMethods'] as $iframePaymentMethod) {
                $array[] = $this->denormalizer->denormalize(
                    $iframePaymentMethod,
                    PaymentMethod::class,
                    $format,
                    $context
                );
            }
            $obj->iframePaymentMethods = $array;
        }

        return $obj;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed $data Data to denormalize from
     * @param string $type The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === PaymentMethodsSettings::class;
    }

    /**
     * Sets the owning Denormalizer object.
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }
}
