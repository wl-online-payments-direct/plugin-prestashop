<?php
/*
 * This class was auto-generated.
 */

namespace OnlinePayments\Sdk\Domain;

use OnlinePayments\Sdk\DataObject;
use UnexpectedValueException;

/**
 * @package OnlinePayments\Sdk\Domain
 */
class CardPaymentMethodSpecificInputForHostedCheckout extends DataObject
{
    // Properties
    /**
     * @var bool
     */
    private $groupCards;

    /**
     * LOCAL ADDITION - not part of vendored SDK 4.6.0.
     *
     * The platform accepts this field, but the SDK only exposes it from 8.0 onward. It is added
     * here rather than by upgrading the SDK, because the 8.0+ line type-hints every setter and
     * splits the request payloads into X / XBase pairs, which is an API migration for the whole
     * payment flow. Re-apply this property if this SDK is ever replaced wholesale.
     *
     * @var string
     */
    private $tokenizationMode;

    // Methods
    /**
     * @return bool
     */
    public function getGroupCards()
    {
        return $this->groupCards;
    }
    /**
     * @var bool
     */
    public function setGroupCards($value)
    {
        $this->groupCards = $value;
    }

    /**
     * @return string
     */
    public function getTokenizationMode()
    {
        return $this->tokenizationMode;
    }
    /**
     * @var string
     */
    public function setTokenizationMode($value)
    {
        $this->tokenizationMode = $value;
    }

    /**
     * @return object
     */
    public function toObject()
    {
        $object = parent::toObject();
        if ($this->groupCards !== null) {
            $object->groupCards = $this->groupCards;
        }
        if ($this->tokenizationMode !== null) {
            $object->tokenizationMode = $this->tokenizationMode;
        }
        return $object;
    }

    /**
     * @param object $object
     * @return $this
     * @throws UnexpectedValueException
     */
    public function fromObject($object)
    {
        parent::fromObject($object);
        if (property_exists($object, 'groupCards')) {
            $this->groupCards = $object->groupCards;
        }
        if (property_exists($object, 'tokenizationMode')) {
            $this->tokenizationMode = $object->tokenizationMode;
        }
        return $this;
    }
}
