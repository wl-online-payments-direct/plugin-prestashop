<?php
/**
 * 2021 Worldline Online Payments
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

const ACCOUNT_SETTINGS_COLUMN_NAME = 'WORLDLINEOP_ACCOUNT_SETTINGS';

/**
 * Updates module to the version 1.4.12
 * - If 'webhookMode' is missing => set 'manual'
 * - If 'additionalWebhookUrls' is missing => set []
 * - Preserve existing values otherwise
 */
function upgrade_module_1_4_12()
{
    $previousShopContext = Shop::getContext();
    Shop::setContext(Shop::CONTEXT_ALL);

    $sql = 'SELECT id_shop_group, id_shop, value 
            FROM ' . _DB_PREFIX_ . 'configuration 
            WHERE name = "' . pSQL(ACCOUNT_SETTINGS_COLUMN_NAME) . '"';
    $results = Db::getInstance()->executeS($sql);

    if (!is_array($results)) {
        Shop::setContext($previousShopContext);
        return true;
    }

    foreach ($results as $row) {
        if (!isset($row['value']) || $row['value'] === '') {
            continue;
        }

        $acc = json_decode($row['value'], true);
        if (!is_array($acc)) {
            continue;
        }

        $changed = false;

        if (!array_key_exists('webhookMode', $acc)) {
            $acc['webhookMode'] = 'manual';
            $changed = true;
        }

        if (!array_key_exists('additionalWebhookUrls', $acc)) {
            $acc['additionalWebhookUrls'] = [];
            $changed = true;
        } elseif (!is_array($acc['additionalWebhookUrls'])) {
            $acc['additionalWebhookUrls'] = [];
            $changed = true;
        }

        if ($changed) {
            Configuration::updateValue(
                ACCOUNT_SETTINGS_COLUMN_NAME,
                json_encode($acc),
                false,
                (int) $row['id_shop_group'],
                (int) $row['id_shop']
            );
        }
    }

    Shop::setContext($previousShopContext);
    return true;
}
