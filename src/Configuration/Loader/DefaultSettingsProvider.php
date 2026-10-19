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

namespace WorldlineOP\PrestaShop\Configuration\Loader;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Yaml\Parser;
use WorldlineOP\PrestaShop\OptionsResolver\AccountSettingsResolver;
use WorldlineOP\PrestaShop\OptionsResolver\AdvancedSettingsResolver;
use WorldlineOP\PrestaShop\OptionsResolver\PaymentMethodsSettingsResolver;

/**
 * Class DefaultSettingsProvider
 */
class DefaultSettingsProvider
{
    /** @var array|null */
    private $yaml;

    /**
     * DefaultSettingsProvider constructor.
     */
    public function __construct()
    {
        $this->yaml = null;
    }

    /**
     * @return array
     */
    public function getYaml()
    {
        if (null === $this->yaml) {
            $parser = new Parser();
            $this->yaml = $parser->parse(\Tools::file_get_contents(dirname(__DIR__, 3) . '/install/defaults.yml'));
        }

        return $this->yaml;
    }

    /**
     * @return array
     */
    public function getAccountSettings(): array
    {
        $accountSettings = $this->getConfigurationSection('accountSettings');
        $accountSettings['webhookMode'] = 'automatic';
        $accountSettings['additionalWebhookUrls'] = [];

        // Resolving casts the values to the types the settings entities expect
        return (new AccountSettingsResolver())->resolve($accountSettings);
    }

    /**
     * @return array
     */
    public function getAdvancedSettings(): array
    {
        $advancedSettings = $this->getConfigurationSection('advancedSettings');
        $advancedSettings['paymentSettings']['successOrderStateId'] = \Configuration::get('PS_OS_PAYMENT');
        $advancedSettings['paymentSettings']['pendingOrderStateId'] = \Configuration::getGlobalValue('WOP_PENDING_ORDER_STATUS_ID');
        $advancedSettings['paymentSettings']['errorOrderStateId'] = \Configuration::get('PS_OS_ERROR');

        return (new AdvancedSettingsResolver())->resolve($advancedSettings);
    }

    /**
     * @return array
     */
    public function getPaymentMethodsSettings(): array
    {
        return (new PaymentMethodsSettingsResolver())->resolve($this->getConfigurationSection('paymentMethodsSettings'));
    }

    /**
     * @param string $section
     *
     * @return array
     */
    private function getConfigurationSection($section)
    {
        $yaml = $this->getYaml();
        if (!isset($yaml['configuration'][$section]) || !is_array($yaml['configuration'][$section])) {
            return [];
        }

        return $yaml['configuration'][$section];
    }
}
