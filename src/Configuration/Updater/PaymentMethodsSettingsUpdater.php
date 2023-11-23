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

use Exception;
use WorldlineOP\PrestaShop\Configuration\Entity\PaymentMethodsSettings;

/**
 * Class PaymentMethodsSettingsUpdater
 */
class PaymentMethodsSettingsUpdater extends SettingsUpdater
{
    protected function serialize()
    {
        $this->json = $this->serializer->serialize($this->settings->paymentMethodsSettings, 'json');
    }

    protected function save()
    {
        \Configuration::updateValue('WORLDLINEOP_PAYMENT_METHODS_SETTINGS', $this->json);
    }

    /**
     * @param array $array
     */
    protected function denormalize($array)
    {
        $this->serializer->denormalize($array, PaymentMethodsSettings::class, null, ['object_to_populate' => $this->settings->paymentMethodsSettings]);
    }

    /**
     * @param bool $deleteLogo
     *
     * @throws Exception
     */
    public function updateIframeLogo($deleteLogo)
    {
        if ($deleteLogo) {
            $file = _PS_MODULE_DIR_ . $this->module->name . '/views/img/payment_logos/' . $this->settings->paymentMethodsSettings->iframeLogoFilename;
            if (realpath($file) === $file) {
                unlink($file);
            }
            $this->denormalize(['iframeLogoFilename' => '']);
            $this->serialize();
            $this->save();

            return;
        }
        if (!isset($_FILES['worldlineopPaymentMethodsSettings']['error']['iframeLogo']) ||
            is_array($_FILES['worldlineopPaymentMethodsSettings']['error']['iframeLogo'])
        ) {
            //@formatter:off
            throw new Exception($this->module->l('Error while uploading subscription logo', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }
        switch ($_FILES['worldlineopPaymentMethodsSettings']['error']['iframeLogo']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                //@formatter:off
                throw new Exception($this->module->l('Exceeded filesize limit for logo.', 'PaymentMethodsSettingsUpdater'));
                //@formatter:on
            default:
                //@formatter:off
                throw new Exception($this->module->l('Logo: Unknown error.', 'PaymentMethodsSettingsUpdater'));
                //@formatter:on
        }
        $source = $_FILES['worldlineopPaymentMethodsSettings']['tmp_name']['iframeLogo'];
        list($width, $height, $fileType) = getimagesize($source);
        if (!in_array($fileType, $this->authorizedLogoExtensions)) {
            //@formatter:off
            throw new Exception($this->module->l('Logo: You must submit .png, .gif, or .jpg files only.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }
        $filename = sprintf('%s.%s', md5(time()), array_search($fileType, $this->authorizedLogoExtensions));
        $file = _PS_MODULE_DIR_ . $this->module->name . '/views/img/payment_logos/' . $filename;
        if (!move_uploaded_file($source, $file)) {
            //@formatter:off
            throw new Exception($this->module->l('Cannot upload logo.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }

        $this->denormalize(['iframeLogoFilename' => $filename]);
        $this->serialize();
        $this->save();
    }

    /**
     * @param bool $deleteLogo
     *
     * @throws Exception
     */
    public function updateGenericLogo($deleteLogo)
    {
        if ($deleteLogo) {
            $file = _PS_MODULE_DIR_ . $this->module->name . '/views/img/payment_logos/' . $this->settings->paymentMethodsSettings->genericLogoFilename;
            if (realpath($file) === $file) {
                unlink($file);
            }
            $this->denormalize(['genericLogoFilename' => '']);
            $this->serialize();
            $this->save();

            return;
        }
        if (!isset($_FILES['worldlineopPaymentMethodsSettings']['error']['genericLogo']) ||
            is_array($_FILES['worldlineopPaymentMethodsSettings']['error']['genericLogo'])
        ) {
            //@formatter:off
            throw new Exception($this->module->l('Error while uploading subscription logo', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }
        switch ($_FILES['worldlineopPaymentMethodsSettings']['error']['genericLogo']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                //@formatter:off
                throw new Exception($this->module->l('Exceeded filesize limit for logo.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
            default:
                //@formatter:off
                throw new Exception($this->module->l('Logo: Unknown error.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }
        $source = $_FILES['worldlineopPaymentMethodsSettings']['tmp_name']['genericLogo'];
        list($width, $height, $fileType) = getimagesize($source);
        if (!in_array($fileType, $this->authorizedLogoExtensions)) {
            //@formatter:off
            throw new Exception($this->module->l('Logo: You must submit .png, .gif, or .jpg files only.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }
        $filename = sprintf('%s.%s', md5(time()), array_search($fileType, $this->authorizedLogoExtensions));
        $file = _PS_MODULE_DIR_ . $this->module->name . '/views/img/payment_logos/' . $filename;
        if (!move_uploaded_file($source, $file)) {
            //@formatter:off
            throw new Exception($this->module->l('Cannot upload logo.', 'PaymentMethodsSettingsUpdater'));
            //@formatter:on
        }

        $this->denormalize(['genericLogoFilename' => $filename]);
        $this->serialize();
        $this->save();
    }
}
