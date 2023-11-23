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

namespace WorldlineOP\PrestaShop\OptionsResolver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentMethodsSettingsResolver
 */
class PaymentMethodsSettingsResolver extends AbstractSettingsResolver
{
    /**
     * @param OptionsResolver $resolver
     *
     * @return mixed|void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined([
                'displayGenericOption',
                'genericLogoFilename',
                'deleteGenericLogo',
                'redirectTemplateFilename',
                'redirectCallToAction',
                'iframeTemplateFilename',
                'iframeCallToAction',
                'displayRedirectPaymentOptions',
                'displayIframePaymentOptions',
                'redirectPaymentMethods',
                'iframePaymentMethods',
                'iframeLogo',
                'iframeLogoFilename',
                'enabled',
                'productId',
                'identifier',
                'type',
                'logo',
                'deleteLogo',
            ])
            ->setNormalizer(
                'displayGenericOption',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'displayRedirectPaymentOptions',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'displayIframePaymentOptions',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'enabled',
                function (Options $options, $value) {
                    return (bool) $value;
                }
            )
            ->setNormalizer(
                'productId',
                function (Options $options, $value) {
                    return (int) $value;
                }
            );
    }

    /**
     * @param array $array
     *
     * @return array|mixed
     */
    public function resolve($array)
    {
        $resolvedArray = $this->resolver->resolve($array);
        $redirectPaymentMethods = [];
        $iframePaymentMethods = [];
        if (isset($resolvedArray['redirectPaymentMethods']) && !empty($resolvedArray['redirectPaymentMethods'])) {
            foreach ($resolvedArray['redirectPaymentMethods'] as $paymentMethod) {
                $redirectPaymentMethods[] = $this->resolver->resolve($paymentMethod);
            }
        }
        if (isset($resolvedArray['iframePaymentMethods']) && !empty($resolvedArray['iframePaymentMethods'])) {
            foreach ($resolvedArray['iframePaymentMethods'] as $paymentMethod) {
                $iframePaymentMethods[] = $this->resolver->resolve($paymentMethod);
            }
        }
        $resolvedArray['redirectPaymentMethods'] = $redirectPaymentMethods;
        $resolvedArray['iframePaymentMethods'] = $iframePaymentMethods;

        return $resolvedArray;
    }
}
