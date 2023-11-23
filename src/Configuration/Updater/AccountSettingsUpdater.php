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

use WorldlineOP\PrestaShop\Configuration\Entity\AccountSettings;

/**
 * Class AccountSettingsUpdater
 */
class AccountSettingsUpdater extends SettingsUpdater
{
    protected function serialize()
    {
        $this->json = $this->serializer->serialize($this->settings->accountSettings, 'json');
    }

    protected function save()
    {
        \Configuration::updateValue('WORLDLINEOP_ACCOUNT_SETTINGS', $this->json);
    }

    /**
     * @param array $array
     */
    protected function denormalize($array)
    {
        $this->serializer->denormalize($array, AccountSettings::class, null, ['object_to_populate' => $this->settings->accountSettings]);
    }

    /**
     * @param array $array
     * @param AccountSettings $object
     *
     * @return array|object
     */
    public function forceDenormalize($array, $object)
    {
        return $this->serializer->denormalize($array, AccountSettings::class, null, ['object_to_populate' => $object]);
    }

    /**
     * @param array $array
     *
     * @return array|mixed
     */
    public function forceResolve($array)
    {
        return $this->resolver->resolve($array);
    }
}
