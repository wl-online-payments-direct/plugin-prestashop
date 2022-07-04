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

namespace WorldlineOP\PrestaShop\Configuration\Validation;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use WorldlineOP\PrestaShop\Configuration\Entity\AccountSettings;

/**
 * Class AccountValidationData
 * @package WorldlineOP\PrestaShop\Configuration\Validation
 */
class AccountValidationData extends AbstractValidationData
{
    /**
     * @param array $array
     * @return array
     */
    public function getValidationData($array)
    {
        //@formatter:off
        $constraints = [
            'environment' => new Choice(['choices' => [AccountSettings::ACCOUNT_MODE_TEST, AccountSettings::ACCOUNT_MODE_PROD], 'message' => $this->module->l('The environment value is not valid', 'AccountValidationData')]),
            'testApiKey' => [
                new Length(['min' => AccountSettings::API_KEY_LENGTH_MIN, 'max' => AccountSettings::API_KEY_LENGTH_MAX, 'exactMessage' => $this->module->l('Test API key must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-zA-Z0-9]+$/i', 'message' => $this->module->l('Please enter a valid test API key', 'AccountValidationData')]),
            ],
            'testWebhooksKey' => [
                new Length(['min' => AccountSettings::WEBHOOKS_KEY_LENGTH, 'max' => AccountSettings::WEBHOOKS_KEY_LENGTH, 'exactMessage' => $this->module->l('Test webhooks key must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-f0-9]+$/i', 'message' => $this->module->l('Please enter a valid test Webhooks key', 'AccountValidationData')]),
            ],
            'testWebhooksSecret' => [
                new Length(['min' => AccountSettings::WEBHOOKS_SECRET_LENGTH, 'max' => AccountSettings::WEBHOOKS_SECRET_LENGTH, 'exactMessage' => $this->module->l('Test webhooks secret must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-f0-9\-]+$/i', 'message' => $this->module->l('Please enter a valid test API secret', 'AccountValidationData')]),
            ],
            'prodApiKey' => [
                new Length(['min' => AccountSettings::API_KEY_LENGTH_MIN, 'max' => AccountSettings::API_KEY_LENGTH_MAX, 'exactMessage' => $this->module->l('Prod API key must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-zA-Z0-9]+$/i', 'message' => $this->module->l('Please enter a valid prod API key', 'AccountValidationData')]),
            ],
            'prodWebhooksKey' => [
                new Length(['min' => AccountSettings::WEBHOOKS_KEY_LENGTH, 'max' => AccountSettings::WEBHOOKS_KEY_LENGTH, 'exactMessage' => $this->module->l('Prod webhooks key must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-f0-9]+$/i', 'message' => $this->module->l('Please enter a valid test Webhooks key', 'AccountValidationData')]),
            ],
            'prodWebhooksSecret' => [
                new Length(['min' => AccountSettings::WEBHOOKS_SECRET_LENGTH, 'max' => AccountSettings::WEBHOOKS_SECRET_LENGTH, 'exactMessage' => $this->module->l('Prod webhooks secret must be exactly {{ limit }} characters long', 'AccountValidationData')]),
                new Regex(['pattern' => '/^[a-f0-9\-]+$/i', 'message' => $this->module->l('Please enter a valid test Webhooks secret', 'AccountValidationData')]),
            ],
        ];
        //@formatter:on

        $arrayToValidate = array_intersect_key($array, $constraints);
        $validationConstraints = array_intersect_key($constraints, $array);

        return ['array' => $arrayToValidate, 'constraints' => $validationConstraints];
    }
}
