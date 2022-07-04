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

namespace WorldlineOP\PrestaShop\Configuration\Updater;

use Exception;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use WorldlineOP\PrestaShop\Configuration\Entity\AdvancedSettings;

/**
 * Class AdvancedSettingsUpdater
 * @package WorldlineOP\PrestaShop\Configuration\Updater
 */
class AdvancedSettingsUpdater extends SettingsUpdater
{
    /**
     *
     */
    protected function serialize()
    {
        $this->json = $this->serializer->serialize($this->settings->advancedSettings, 'json');
    }

    /**
     *
     */
    protected function save()
    {
        \Configuration::updateValue('WORLDLINEOP_ADVANCED_SETTINGS', $this->json);
    }

    /**
     * @param array $array
     */
    protected function denormalize($array)
    {
        $this->serializer->denormalize($array, AdvancedSettings::class, null, [AbstractNormalizer::OBJECT_TO_POPULATE => $this->settings->advancedSettings]);
    }

    /**
     * @param bool $deleteLogo
     * @throws Exception
     */
    public function updateLogo($deleteLogo)
    {
        if ($deleteLogo) {
            $file = _PS_MODULE_DIR_.$this->module->name.'/views/img/payment_logos/'.$this->settings->advancedSettings->endpointLogoFilename;
            if (realpath($file) === $file) {
                unlink($file);
            }
            $this->denormalize(['endpointLogoFilename' => '']);
            $this->serialize();
            $this->save();

            return;
        }
        if (!isset($_FILES['worldlineopAdvancedSettings']['error']['endpointLogo']) ||
            is_array($_FILES['worldlineopAdvancedSettings']['error']['endpointLogo'])
        ) {
            //@formatter:off
            throw new Exception($this->module->l('Error while uploading subscription logo', 'AdvancedSettingsUpdater'));
            //@formatter:on
        }
        switch ($_FILES['worldlineopAdvancedSettings']['error']['endpointLogo']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                //@formatter:off
                throw new Exception($this->module->l('Exceeded filesize limit for logo.', 'AdvancedSettingsUpdater'));
            //@formatter:on
            default:
                //@formatter:off
                throw new Exception($this->module->l('Logo: Unknown error.', 'AdvancedSettingsUpdater'));
            //@formatter:on
        }
        $source = $_FILES['worldlineopAdvancedSettings']['tmp_name']['endpointLogo'];
        list($width, $height, $fileType) = getimagesize($source);
        if (!in_array($fileType, $this->authorizedLogoExtensions)) {
            //@formatter:off
            throw new Exception($this->module->l('Logo: You must submit .png, .gif, or .jpg files only.', 'AdvancedSettingsUpdater'));
            //@formatter:on
        }
        $filename = sprintf('%s.%s', md5(time()), array_search($fileType, $this->authorizedLogoExtensions));
        $file = _PS_MODULE_DIR_.$this->module->name.'/views/img/payment_logos/'.$filename;
        if (!move_uploaded_file($source, $file)) {
            //@formatter:off
            throw new Exception($this->module->l('Cannot upload logo.', 'AdvancedSettingsUpdater'));
            //@formatter:on
        }

        $this->denormalize(['endpointLogoFilename' => $filename]);
        $this->serialize();
        $this->save();
    }
}
