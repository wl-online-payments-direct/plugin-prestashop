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
 * Class AccountSettings
 */
class AccountSettings
{
    const ACCOUNT_MODE_TEST = 'test';
    const ACCOUNT_MODE_PROD = 'prod';
    const API_KEY_LENGTH_MAX = 99;
    const API_KEY_LENGTH_MIN = 1;
    const SECRET_KEY_LENGTH = 48;
    const WEBHOOKS_KEY_LENGTH = 30;
    const WEBHOOKS_SECRET_LENGTH = 36;

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
}
