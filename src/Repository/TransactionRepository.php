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

namespace WorldlineOP\PrestaShop\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TransactionRepository
 */
class TransactionRepository
{
    /**
     * @param int $idOrder
     *
     * @return false|\WorldlineopTransaction
     *
     * @throws \PrestaShopException
     */
    public function findByIdOrder($idOrder)
    {
        $collection = new \PrestaShopCollection('WorldlineopTransaction');
        $collection->where('id_order', '=', (int) $idOrder);

        /** @var \WorldlineopTransaction|false $transaction */
        $transaction = $collection->getFirst();

        return $transaction;
    }

    /**
     * @param \WorldlineopTransaction $transaction
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function save(\WorldlineopTransaction $transaction)
    {
        return $transaction->save();
    }
}
