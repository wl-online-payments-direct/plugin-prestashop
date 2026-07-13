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
 * Class TokenRepository
 */
class TokenRepository
{
    /**
     * @param int $idToken
     *
     * @return false|\WorldlineopToken
     *
     * @throws \PrestaShopException
     */
    public function findById($idToken)
    {
        $collection = new \PrestaShopCollection('WorldlineopToken');
        $collection->where('id_worldlineop_token', '=', (int) $idToken);

        /** @var \WorldlineopToken|false $token */
        $token = $collection->getFirst();

        return $token;
    }

    /**
     * @param int $idCustomer
     * @param string $secureKey
     * @param int $idShop
     *
     * @return \PrestaShopCollection
     *
     * @throws \PrestaShopException
     */
    public function findByIdCustomerIdShop($idCustomer, $secureKey, $idShop)
    {
        $collection = new \PrestaShopCollection('WorldlineopToken');
        $collection->where('id_customer', '=', (int) $idCustomer);
        $collection->where('id_shop', '=', (int) $idShop);
        $collection->where('secure_key', '=', pSQL($secureKey));

        $tokens = $collection->getAll();

        return $tokens;
    }

    /**
     * @param int $idCustomer
     * @param string $tokenValue
     *
     * @return false|\WorldlineopToken
     *
     * @throws \PrestaShopException
     */
    public function findByCustomerIdToken($idCustomer, $tokenValue)
    {
        $collection = new \PrestaShopCollection('WorldlineopToken');
        $collection->where('id_customer', '=', (int) $idCustomer);
        $collection->where('value', '=', pSQL($tokenValue));

        /** @var \WorldlineopToken|false $token */
        $token = $collection->getFirst();

        return $token;
    }

    /**
     * @param int $idCustomer
     *
     * @throws \PrestaShopException
     */
    public function deleteByIdCustomer($idCustomer)
    {
        $collection = new \PrestaShopCollection('WorldlineopToken');
        $collection->where('id_customer', '=', (int) $idCustomer);

        $tokens = $collection->getAll();
        foreach ($tokens as $token) {
            $token->delete();
        }
    }

    /**
     * @param \WorldlineopToken $token
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function save(\WorldlineopToken $token)
    {
        return $token->save();
    }

    /**
     * @param \WorldlineopToken $token
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function delete(\WorldlineopToken $token)
    {
        return $token->delete();
    }
}
