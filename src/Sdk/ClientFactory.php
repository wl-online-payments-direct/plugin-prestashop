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

use OnlinePayments\Sdk\Client;
use OnlinePayments\Sdk\Communicator;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;

/**
 * Class ClientFactory
 */
class ClientFactory
{
    /** @var Communicator */
    private $communicator;

    /** @var Settings */
    private $settings;

    /**
     * ClientFactory constructor.
     *
     * @param Communicator $communicator
     * @param Settings $settings
     */
    public function __construct(Communicator $communicator, Settings $settings)
    {
        $this->communicator = $communicator;
        $this->settings = $settings;
    }

    /**
     * @return \OnlinePayments\Sdk\Merchant\MerchantClient|\OnlinePayments\Sdk\Merchant\MerchantClientInterface
     */
    public function getMerchant()
    {
        $client = new Client($this->communicator);

        return $client->merchant($this->settings->credentials->pspid);
    }

    /**
     * @param Settings $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
