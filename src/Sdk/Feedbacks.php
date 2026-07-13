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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Feedbacks
{
    /**
     * @var array
     */
    private $webhooksUrls = [];

    /**
     * @return array
     */
    public function getWebhooksUrls()
    {
        return $this->webhooksUrls;
    }

    /**
     * @param array $webhooksUrls
     *
     * @return $this
     */
    public function setWebhooksUrls(array $webhooksUrls)
    {
        $this->webhooksUrls = $webhooksUrls;

        return $this;
    }

    public function toObject()
    {
        $object = new \stdClass();
        $object->webhooksUrls = $this->webhooksUrls;

        return $object;
    }

    public function fromObject($object)
    {
        if (property_exists($object, 'webhooksUrls') && is_array($object->webhooksUrls)) {
            $this->webhooksUrls = $object->webhooksUrls;
        }

        return $this;
    }
}
