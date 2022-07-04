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
 *
 */

namespace WorldlineOP\PrestaShop\Configuration\Entity;

/**
 * Class PaymentSettings
 * @package WorldlineOP\PrestaShop\Configuration\Entity
 */
class PaymentSettings
{
    const TRANSACTION_TYPE_IMMEDIATE = 'SALE';
    const TRANSACTION_TYPE_AUTH = 'FINAL_AUTHORIZATION';
    const CAPTURE_DELAY_MIN = 0;
    const CAPTURE_DELAY_MAX = 7;
    const RETENTION_DELAY_MIN = 3;
    const RETENTION_DELAY_MAX = 24;

    /** @var string $transactionType */
    public $transactionType;

    /** @var int $captureDelay */
    public $captureDelay;

    /** @var int $retentionHours */
    public $retentionHours;

    /** @var int $successOrderStateId */
    public $successOrderStateId;

    /** @var int $pendingOrderStateId */
    public $pendingOrderStateId;

    /** @var int $errorOrderStateId */
    public $errorOrderStateId;
}
