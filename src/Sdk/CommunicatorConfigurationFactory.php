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

namespace WorldlineOP\PrestaShop\Sdk;

use OnlinePayments\Sdk\CommunicatorConfiguration;
use OnlinePayments\Sdk\Domain\ShoppingCartExtension;
use Worldlineop;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;

/**
 * Class CommunicatorConfigurationFactory
 */
class CommunicatorConfigurationFactory
{
    /** @var Settings */
    private $settings;

    /** @var Worldlineop */
    private $module;

    /**
     * CommunicatorConfigurationFactory constructor.
     *
     * @param Settings $settings
     * @param Worldlineop $module
     */
    public function __construct(Settings $settings, Worldlineop $module)
    {
        $this->settings = $settings;
        $this->module = $module;
    }

    /**
     * @return CommunicatorConfiguration
     */
    public function getInstance()
    {
        $communicator = new CommunicatorConfiguration(
            $this->settings->credentials->apiKey,
            $this->settings->credentials->apiSecret,
            $this->settings->credentials->endpoint,
            'PrestaShop'
        );
        $shoppingCartExtension = new ShoppingCartExtension(
            'Evolutive Group',
            'PrestaShop Plugin',
            $this->module->version
        );
        $shoppingCartExtension->setExtensionId(sprintf('PSdirectv%s', $this->module->version));
        $communicator->setShoppingCartExtension($shoppingCartExtension);

        return $communicator;
    }
}
