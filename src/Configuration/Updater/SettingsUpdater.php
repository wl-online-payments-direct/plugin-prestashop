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

namespace WorldlineOP\PrestaShop\Configuration\Updater;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Configuration\Validation\AbstractValidationData;
use WorldlineOP\PrestaShop\Exception\ExceptionList;
use WorldlineOP\PrestaShop\OptionsResolver\AbstractSettingsResolver;

/**
 * Class SettingsUpdater
 */
abstract class SettingsUpdater
{
    /** @var array */
    protected $authorizedLogoExtensions = ['png' => IMAGETYPE_PNG, 'gif' => IMAGETYPE_GIF, 'jpg' => IMAGETYPE_JPEG];

    /** @var Serializer */
    protected $serializer;

    /** @var OptionsResolver */
    protected $resolver;

    /** @var Settings */
    protected $settings;

    /** @var AbstractValidationData */
    protected $validationData;

    /** @var \Worldlineop */
    protected $module;

    /** @var string */
    protected $json;

    /** @var ConstraintViolationList */
    private $violations;

    /**
     * SettingsUpdater constructor.
     *
     * @param Serializer $serializer
     * @param AbstractSettingsResolver $resolver
     * @param Settings $settings
     * @param AbstractValidationData $validationData
     * @param \Worldlineop $module
     */
    public function __construct(
        Serializer $serializer,
        AbstractSettingsResolver $resolver,
        Settings $settings,
        AbstractValidationData $validationData,
        \Worldlineop $module
    ) {
        $this->serializer = $serializer;
        $this->resolver = $resolver;
        $this->settings = $settings;
        $this->validationData = $validationData;
        $this->module = $module;
    }

    /**
     * @param array $array
     *
     * @return Settings
     *
     * @throws ExceptionList
     */
    public function update($array)
    {
        $array = $this->resolver->resolve($array);
        $this->validate($array);
        $this->denormalize($array);
        $this->serialize();
        $this->save();

        return $this->settings;
    }

    /**
     * @param array $array
     *
     * @return void
     *
     * @throws ExceptionList
     */
    public function validate($array)
    {
        $validationData = $this->validationData->getValidationData($array);
        $validator = Validation::createValidator();
        $this->violations = $validator->validate($validationData['array'], new Collection($validationData['constraints']));
        $exceptions = [];
        foreach ($this->violations as $violation) {
            $exceptions[] = new \Exception($violation->getMessage());
        }

        if (!empty($exceptions)) {
            $exceptionList = new ExceptionList('Error while validating account settings data');
            $exceptionList->setExceptions($exceptions);
            throw $exceptionList;
        }
    }

    /**
     * @return ConstraintViolationList
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param array $array
     *
     * @return mixed
     */
    abstract protected function denormalize($array);

    /**
     * @return void
     */
    abstract protected function serialize();

    /**
     * @return void
     */
    abstract protected function save();
}
