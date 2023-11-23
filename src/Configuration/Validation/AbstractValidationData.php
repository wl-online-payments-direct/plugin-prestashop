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

namespace WorldlineOP\PrestaShop\Configuration\Validation;

/**
 * Class AbstractValidationData
 */
abstract class AbstractValidationData implements ValidationDataInterface
{
    /** @var \Worldlineop */
    protected $module;

    /**
     * AbstractValidationData constructor.
     *
     * @param \Worldlineop $module
     */
    public function __construct(\Worldlineop $module)
    {
        $this->module = $module;
    }
}
