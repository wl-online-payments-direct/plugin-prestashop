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
 * Class AccountSettingsResolver
 */
class AccountSettingsResolver extends AbstractSettingsResolver
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
                'environment',
                'testApiKey',
                'testApiSecret',
                'testWebhooksKey',
                'testWebhooksSecret',
                'testPspid',
                'prodApiKey',
                'prodApiSecret',
                'prodWebhooksKey',
                'prodWebhooksSecret',
                'prodPspid',
            ])
            ->setNormalizer(
                'testApiKey',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'testApiSecret',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'testWebhooksKey',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'testWebhooksSecret',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'testPspid',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'prodApiKey',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'prodApiSecret',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'prodWebhooksKey',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'prodWebhooksSecret',
                function (Options $options, $value) {
                    return trim($value);
                }
            )
            ->setNormalizer(
                'prodPspid',
                function (Options $options, $value) {
                    return trim($value);
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
        return $this->resolver->resolve($array);
    }
}
