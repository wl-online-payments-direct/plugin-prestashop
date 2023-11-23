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

namespace WorldlineOP\PrestaShop\Configuration\Validation;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AdvancedSettingsValidationData
 */
class AdvancedSettingsValidationData extends AbstractValidationData
{
    /**
     * @param array $array
     *
     * @return array
     */
    public function getValidationData($array)
    {
        //@formatter:off
        $constraints = [
            'testEndpoint' => [
                new Assert\Url([
                    'protocols' => ['https'],
                    'message' => $this->module->l('Test endpoint value must be a valid https URL', 'PaymentMethodsValidationData'),
                    'checkDNS' => Assert\Url::CHECK_DNS_TYPE_A,
                    'dnsMessage' => $this->module->l('The test endpoint host could not be resolved', 'PaymentMethodsValidationData'),
                ]),
            ],
            'prodEndpoint' => [
                new Assert\Url([
                    'protocols' => ['https'],
                    'message' => $this->module->l('Prod endpoint value must be valid a https URL', 'PaymentMethodsValidationData'),
                    'checkDNS' => Assert\Url::CHECK_DNS_TYPE_A,
                    'dnsMessage' => $this->module->l('The production endpoint host could not be resolved', 'PaymentMethodsValidationData'),
                ]),
            ],
        ];
        //@formatter:on

        $arrayToValidate = array_intersect_key($array, $constraints);
        $validationConstraints = array_intersect_key($constraints, $array);

        return ['array' => $arrayToValidate, 'constraints' => $validationConstraints];
    }
}
