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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractSettingsResolver
 */
abstract class AbstractSettingsResolver implements ParameterResolverInterface
{
    /** @var OptionsResolver */
    protected $resolver;

    /**
     * AbstractSettingsResolver constructor.
     */
    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
    }

    /**
     * @param array $array
     *
     * @return array|mixed
     */
    abstract public function resolve($array);

    /**
     * @param OptionsResolver $resolver
     *
     * @return mixed
     */
    abstract public function configureOptions(OptionsResolver $resolver);
}
