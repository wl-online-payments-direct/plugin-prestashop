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
 * Class AccountSettings
 * @package WorldlineOP\PrestaShop\Configuration\Entity
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

    /** @var string $environment */
    public $environment;

    /** @var string $testPspid */
    public $testPspid;

    /** @var string $testApiKey */
    public $testApiKey;

    /** @var string $testApiSecret */
    public $testApiSecret;

    /** @var string $testWebhooksKey */
    public $testWebhooksKey;

    /** @var string $testWebhooksSecret */
    public $testWebhooksSecret;

    /** @var string $prodPspid */
    public $prodPspid;

    /** @var string $prodApiKey */
    public $prodApiKey;

    /** @var string $prodApiSecret */
    public $prodApiSecret;

    /** @var string $prodWebhooksKey */
    public $prodWebhooksKey;

    /** @var string $prodWebhooksSecret */
    public $prodWebhooksSecret;
}
