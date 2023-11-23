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

use Db;

/**
 * Class TransactionRepository
 */
class TransactionRepository
{
    /** @var Db */
    private $db;

    /** @var array */
    private $cache;

    /**
     * TransactionRepository constructor.
     *
     * @param Db $db
     */
    public function __construct(Db $db = null)
    {
        if (null === $db) {
            $this->db = Db::getInstance();
        } else {
            $this->db = $db;
        }
    }

    /**
     * @param int $idOrder
     *
     * @return false|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByIdOrder($idOrder)
    {
        $collection = new \PrestaShopCollection('WorldlineopTransaction');
        $collection->where('id_order', '=', (int) $idOrder);

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
