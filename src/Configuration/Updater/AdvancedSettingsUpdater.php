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

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use WorldlineOP\PrestaShop\Configuration\Entity\AdvancedSettings;

/**
 * Class AdvancedSettingsUpdater
 */
class AdvancedSettingsUpdater extends SettingsUpdater
{
    protected function serialize()
    {
        $this->json = $this->serializer->serialize($this->settings->advancedSettings, 'json');
    }

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
}
