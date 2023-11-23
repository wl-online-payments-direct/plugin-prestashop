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

namespace WorldlineOP\PrestaShop\Configuration\Entity;

/**
 * Class PaymentSettings
 */
class PaymentSettings
{
    const TRANSACTION_TYPE_IMMEDIATE = 'SALE';
    const TRANSACTION_TYPE_AUTH = 'FINAL_AUTHORIZATION';
    const CAPTURE_DELAY_MIN = 0;
    const CAPTURE_DELAY_MAX = 7;
    const RETENTION_DELAY_MIN = 3;
    const RETENTION_DELAY_MAX = 24;
    const SAFETY_DELAY_MIN = 6;
    const SAFETY_DELAY_MAX = 20;

    /** @var string */
    public $transactionType;

    /** @var int */
    public $captureDelay;

    /** @var int */
    public $retentionHours;

    /** @var int */
    public $successOrderStateId;

    /** @var int */
    public $pendingOrderStateId;

    /** @var int */
    public $safetyDelay;

    /** @var int */
    public $errorOrderStateId;
}
