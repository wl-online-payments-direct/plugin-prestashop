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

use Context;
use Order;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Worldlineop;
use WorldlineOP\PrestaShop\Logger\LoggerFactory;
use WorldlineOP\PrestaShop\Presenter\TransactionPresented;
use WorldlineOP\PrestaShop\Repository\TokenRepository;
use WorldlineOP\PrestaShop\Utils\Tools;
use WorldlineopToken;
use WorldlineopTransaction;

/**
 * Class TransactionResponseProcessor
 */
class TransactionResponseProcessor
{
    /** @var Worldlineop */
    private $module;

    /** @var \Monolog\Logger */
    private $logger;

    /**
     * TransactionResponseProcessor constructor.
     *
     * @param Worldlineop $module
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(Worldlineop $module, LoggerFactory $loggerFactory)
    {
        $this->module = $module;
        $this->logger = $loggerFactory->setChannel('TransactionProcessor');
    }

    /**
     * @param TransactionPresented $presentedData
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function process(TransactionPresented $presentedData)
    {
        if ($presentedData->validateOrder) {
            try {
                $store = new FlockStore();
                $factory = new Factory($store);
                $lock = $factory->createLock($presentedData->payments['merchantReference']);
                if (!$lock->acquire(true)) {
                    $this->logger->debug('Lock cannot be acquired', ['presentedData' => $presentedData]);

                    return;
                }
                $this->logger->debug('Validating order');
                $this->module->validateOrder(
                    (int) $presentedData->cardDetails['idCart'],
                    (int) $presentedData->idOrderState,
                    (float) $presentedData->cardDetails['total'],
                    $presentedData->transaction['paymentMethod'],
                    null,
                    $presentedData->transaction['details'],
                    $presentedData->transaction['idCurrency'],
                    false,
                    $presentedData->cardDetails['secureKey']
                );

                $lock->release();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['presentedData' => $presentedData, 'trace' => $e->getTraceAsString()]);
            }
            if (false !== ($orderIds = Tools::getOrderIdsByIdCart($presentedData->cardDetails['idCart']))) {
                foreach ($orderIds as $idOrder) {
                    $this->logger->debug(sprintf('Saving transaction for order %d', $idOrder));
                    /** @var \WorldlineOP\PrestaShop\Repository\TransactionRepository $transactionRepository */
                    $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
                    $transaction = new WorldlineopTransaction();
                    $transaction->reference = pSQL($presentedData->transaction['merchantReference']);
                    $transaction->id_order = (int) $idOrder;
                    try {
                        $transactionRepository->save($transaction);
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                    if ($presentedData->sendMail) {
                        $this->logger->debug('Sending mail');
                        try {
                            Tools::sendPendingCaptureMail($idOrder);
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());
                        }
                    }
                }
            }
        } elseif ($presentedData->updateStatus) {
            foreach ($presentedData->order['ids'] as $idOrder) {
                $order = new Order((int) $idOrder);
                if (!count($order->getHistory(Context::getContext()->language->id, $presentedData->idOrderState))) {
                    $orderHistory = new \OrderHistory();
                    $orderHistory->id_order = (int) $idOrder;
                    try {
                        $orderHistory->changeIdOrderState($presentedData->idOrderState, $idOrder);
                        $orderHistory->addWithemail();
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                    if (count($order->getOrderPayments()) > count($presentedData->payments['hasPayments'])) {
                        \Db::getInstance()->update(
                            'order_payment',
                            ['transaction_id' => $presentedData->payments['merchantReference']],
                            'order_reference = "' . pSQL($order->reference) . '"'
                        );
                    }
                    if ($presentedData->sendMail) {
                        $this->logger->debug('Sending mail');
                        Tools::sendPendingCaptureMail($order->id);
                    }
                }
            }
        }
        if (isset($presentedData->token['needSave']) && $presentedData->token['needSave']) {
            /** @var TokenRepository $tokenRepository */
            $tokenRepository = $this->module->getService('worldlineop.repository.token');
            $token = $tokenRepository->findByCustomerIdToken(
                $presentedData->cardDetails['idCustomer'],
                $presentedData->token['value']
            );
            if (false === $token) {
                $token = new WorldlineopToken();
            }
            $this->logger->debug('Saving token');
            $token->id_customer = (int) $presentedData->cardDetails['idCustomer'];
            $token->id_shop = (int) $presentedData->token['idShop'];
            $token->product_id = pSQL($presentedData->transaction['productId']);
            $token->card_number = pSQL($presentedData->token['cardNumber']);
            $token->expiry_date = pSQL($presentedData->token['expiryDate']);
            $token->value = pSQL($presentedData->token['value']);
            $token->secure_key = pSQL($presentedData->cardDetails['secureKey']);
            $tokenRepository->save($token);
        }
    }
}
