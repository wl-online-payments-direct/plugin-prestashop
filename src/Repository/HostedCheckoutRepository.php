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
 * Class HostedCheckoutRepository
 */
class HostedCheckoutRepository
{
    /** @var Db */
    private $db;

    /** @var array */
    private $cache;

    /**
     * HostedCheckoutRepository constructor.
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
     * @param string $checksum
     * @param int $idCart
     * @param int $idProduct
     * @param int $idToken
     *
     * @return false|mixed|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByChecksumIdCartIdProductIdToken($checksum, $idCart, $idProduct, $idToken)
    {
        $cacheKey = sprintf('generic_%s_%d_%d_%d', $checksum, (int) $idCart, (int) $idProduct, (int) $idToken);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        $collection = new \PrestaShopCollection('HostedCheckout');
        $collection
            ->where('id_token', '=', (int) $idToken)
            ->where('id_payment_product', '=', (int) $idProduct)
            ->where('checksum', '=', pSQL($checksum))
            ->where('id_cart', '=', (int) $idCart);

        $hostedCheckout = $collection->getFirst();

        if (false !== $hostedCheckout) {
            $this->cache[$cacheKey] = $hostedCheckout;
        }

        return $hostedCheckout;
    }

    /**
     * @param string $returnMac
     * @param string $hostedCheckoutId
     *
     * @return false|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByReturnMacHostedCheckoutId($returnMac, $hostedCheckoutId)
    {
        $collection = new \PrestaShopCollection('HostedCheckout');
        $collection
            ->where('returnmac', '=', pSQL($returnMac))
            ->where('session_id', '=', pSQL($hostedCheckoutId));

        $hostedCheckout = $collection->getFirst();

        return $hostedCheckout;
    }

    /**
     * @param string $merchantReference
     *
     * @return false|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByMerchantReference($merchantReference)
    {
        $collection = new \PrestaShopCollection('HostedCheckout');
        $collection->where('merchant_reference', '=', pSQL($merchantReference));

        $hostedCheckout = $collection->getFirst();

        return $hostedCheckout;
    }

    /**
     * @param int $idCart
     *
     * @throws \PrestaShopException
     */
    public function deleteByIdCart($idCart)
    {
        $collection = new \PrestaShopCollection('HostedCheckout');
        $collection->where('id_cart', '=', (int) $idCart);

        $hostedCheckouts = $collection->getAll();
        foreach ($hostedCheckouts as $hostedCheckout) {
            $hostedCheckout->delete();
        }
    }

    /**
     * @param \HostedCheckout $hostedCheckout
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function save(\HostedCheckout $hostedCheckout)
    {
        return $hostedCheckout->save();
    }

    /**
     * @param \HostedCheckout $hostedCheckout
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function delete(\HostedCheckout $hostedCheckout)
    {
        return $hostedCheckout->delete();
    }
}
