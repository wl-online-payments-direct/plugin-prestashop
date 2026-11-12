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


namespace WorldlineOP\PrestaShop\Processor;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class CartLock
 *
 * Handles the payment events of one cart one at a time. Presenting a payment (does the cart have an order yet?) and
 * processing it (create the order or update its state) run under the same lock. When only the order creation was
 * locked, a captured webhook could find no order, wait while the authorization_requested webhook created it, and
 * then drop its state change. Each cart has its own lock, so the orders of other carts do not wait for each other.
 */
class CartLock
{
    /**
     * @param string $moduleName
     * @param int $idCart
     * @param callable $callback
     *
     * @return mixed What the callback returns
     */
    public static function run($moduleName, $idCart, callable $callback)
    {
        $factory = new Factory(new FlockStore());
        $lock = $factory->createLock($moduleName . '_cart_' . (int) $idCart);
        $lock->acquire(true);
        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
