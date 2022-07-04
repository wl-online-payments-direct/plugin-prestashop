<?php
namespace Ingenico\Direct\Sdk;

/**
 * Class ValueObfuscator
 *
 * @package Ingenico\Direct\Sdk
 */
class ValueObfuscator
{
    /** */
    const MASK_CHARACTER = '*';

    /**
     * @param string $value
     * @return string
     */
    public function obfuscate($value)
    {
        return empty($value)
            ? $value
            : static::MASK_CHARACTER . mb_strlen($value, 'UTF-8');
    }
}
