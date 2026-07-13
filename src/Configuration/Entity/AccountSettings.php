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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AccountSettings
 */
class AccountSettings
{
    public const ACCOUNT_MODE_TEST = 'test';
    public const ACCOUNT_MODE_PROD = 'prod';
    public const API_KEY_LENGTH_MAX = 99;
    public const API_KEY_LENGTH_MIN = 1;
    public const SECRET_KEY_LENGTH = 48;
    public const WEBHOOKS_KEY_LENGTH = 30;
    public const WEBHOOKS_SECRET_LENGTH = 36;

    /** @var string */
    public $environment;

    /** @var string */
    public $testPspid;

    /** @var string */
    public $testApiKey;

    /** @var string */
    public $testApiSecret;

    /** @var string */
    public $testWebhooksKey;

    /** @var string */
    public $testWebhooksSecret;

    /** @var string */
    public $prodPspid;

    /** @var string */
    public $prodApiKey;

    /** @var string */
    public $prodApiSecret;

    /** @var string */
    public $prodWebhooksKey;

    /** @var string */
    public $prodWebhooksSecret;

    /** @var string */
    public $webhookMode = 'manual';

    /** @var array */
    public $additionalWebhookUrls = [];
}
