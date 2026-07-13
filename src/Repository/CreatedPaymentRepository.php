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
 * Class CreatedPaymentRepository
 */
class CreatedPaymentRepository
{
    /**
     * @param string $returnMac
     * @param string $paymentId
     *
     * @return false|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByReturnMacPaymentId($returnMac, $paymentId)
    {
        $collection = new \PrestaShopCollection('CreatedPayment');
        $collection
            ->where('returnmac', '=', pSQL($returnMac))
            ->where('payment_id', '=', pSQL($paymentId));

        $createdPayment = $collection->getFirst();

        return $createdPayment;
    }

    /**
     * @param string $paymentId
     *
     * @return false|\ObjectModel
     *
     * @throws \PrestaShopException
     */
    public function findByPaymentId($paymentId)
    {
        $collection = new \PrestaShopCollection('CreatedPayment');
        $collection
            ->where('payment_id', '=', pSQL($paymentId));

        $createdPayment = $collection->getFirst();

        return $createdPayment;
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
        $collection = new \PrestaShopCollection('CreatedPayment');
        $collection->where('merchant_reference', '=', pSQL($merchantReference));

        $hostedCheckout = $collection->getFirst();

        return $hostedCheckout;
    }

    /**
     * @param \CreatedPayment $createdPayment
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function save(\CreatedPayment $createdPayment)
    {
        return $createdPayment->save();
    }
}
