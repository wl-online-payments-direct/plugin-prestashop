<?php

namespace WorldlineOP\PrestaShop\Sdk;

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
