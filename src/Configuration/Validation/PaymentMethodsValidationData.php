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

use Symfony\Component\Validator\Constraints\Regex;

/**
 * Class PaymentMethodsValidationData
 */
class PaymentMethodsValidationData extends AbstractValidationData
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
            'iframeTemplateFilename' => new Regex([
                'pattern' => '/^[a-zA-Z0-9_\-\.]+$/i',
                'message' => $this->module->l('Please fill a valid iframe template filename', 'PaymentMethodsValidationData'),
            ]),
            'redirectTemplateFilename' => new Regex([
                'pattern' => '/^[a-zA-Z0-9_\-\.]+$/i',
                'message' => $this->module->l('Please fill a valid redirect template filename', 'PaymentMethodsValidationData'),
            ]),
        ];
        //@formatter:on

        $arrayToValidate = array_intersect_key($array, $constraints);
        $validationConstraints = array_intersect_key($constraints, $array);

        return ['array' => $arrayToValidate, 'constraints' => $validationConstraints];
    }
}
