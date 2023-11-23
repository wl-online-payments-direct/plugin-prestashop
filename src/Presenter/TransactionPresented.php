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

namespace WorldlineOP\PrestaShop\Presenter;

/**
 * Class TransactionPresented
 */
class TransactionPresented
{
    /** @var bool */
    public $validateOrder;

    /** @var bool */
    public $updateStatus;

    /** @var array[] */
    public $order;

    /** @var array */
    public $token;

    /** @var array */
    public $cardDetails;

    /** @var array */
    public $transaction;

    /** @var int */
    public $idOrderState;

    /** @var bool */
    public $sendMail;

    /** @var array */
    public $payments;

    /**
     * TransactionPresented constructor.
     */
    public function __construct()
    {
        $this->validateOrder = false;
        $this->updateStatus = false;
        $this->order = [
            'ids' => [],
        ];
        $this->token = [
            'needSave' => false,
            'value' => '',
            'idShop' => 0,
            'cardNumber' => '',
            'expiryDate' => '',
        ];
        $this->cardDetails = [
            'idCart' => 0,
            'total' => 0,
            'secureKey' => '',
            'idCustomer' => 0,
        ];
        $this->transaction = [
            'productId' => 0,
            'paymentMethod' => '',
            'details' => [
                'transactionId' => '',
            ],
            'idCurrency' => 0,
            'merchantReference' => '',
        ];
        $this->idOrderState = 0;
        $this->sendMail = false;
        $this->payments = [
            'hasPayments' => false,
            'merchantReference' => '',
        ];
    }
}
