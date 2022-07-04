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
 *
 */

use Ingenico\Direct\Sdk\Webhooks\InMemorySecretKeyStore;
use Ingenico\Direct\Sdk\Webhooks\WebhooksHelper;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Repository\TokenRepository;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class WorldlineopWebhookModuleFrontController
 */
class WorldlineopWebhookModuleFrontController extends ModuleFrontController
{

    /** @var Worldlineop $module */
    public $module;

    /** @var \Monolog\Logger $logger */
    public $logger;

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('Webhooks');
        $data = \Tools::file_get_contents('php://input');
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->postRequest($data);
                break;
            case 'GET':
                $this->getRequest();
        }
    }

    /**
     * @param $data
     * @throws Exception
     */
    public function postRequest($data)
    {
        /** @var Settings $settings */
        $settings = $this->module->getService('worldlineop.settings');

        $secretKeyStore = new InMemorySecretKeyStore(array($settings->credentials->webhooksKey => $settings->credentials->webhooksSecret));
        $helper = new WebhooksHelper($secretKeyStore);
        try {
            $event = $helper->unmarshal($data, Tools::getServerHttpHeaders());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            header('HTTP/1.1 200 OK');
            exit;
        }
        $this->logger->debug('Webhook call', ['event' => json_decode($event->toJson(), true)]);
        header('HTTP/1.1 200 OK');

        /** @var \WorldlineOP\PrestaShop\Presenter\WebhookEventPresenter $eventPresenter */
        $eventPresenter = $this->module->getService('worldlineop.event.presenter');
        try {
            $eventPresenter->handlePending($event);
            $data = $eventPresenter->present($event, $this->context->shop->id);
        } catch (Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                ['line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()]
            );
        }

        if ($data['validateOrder']) {
            try {
                if ($data['token']['needSave']) {
                    /** @var TokenRepository $tokenRepository */
                    $tokenRepository = $this->module->getService('worldlineop.repository.token');
                    $token = $tokenRepository->findByCustomerIdToken(
                        $data['cartDetails']['idCustomer'],
                        $data['token']['value']
                    );
                    if (false === $token) {
                        $token = new WorldlineopToken();
                    }
                    $this->logger->debug('Saving token');
                    $token->id_customer = (int) $data['cartDetails']['idCustomer'];
                    $token->id_shop = (int) $data['token']['idShop'];
                    $token->product_id = pSQL($data['transaction']['productId']);
                    $token->card_number = pSQL($data['token']['cardNumber']);
                    $token->expiry_date = pSQL($data['token']['expiryDate']);
                    $token->value = pSQL($data['token']['value']);
                    $token->secure_key = pSQL($data['cartDetails']['secureKey']);
                    $tokenRepository->save($token);
                }
                $this->logger->debug('Validating order');
                $this->module->validateOrder(
                    (int) $data['cartDetails']['idCart'],
                    (int) $data['idOrderState'],
                    (float) $data['cartDetails']['total'],
                    $data['transaction']['paymentMethod'],
                    null,
                    $data['transaction']['details'],
                    $data['transaction']['idCurrency'],
                    false,
                    $data['cartDetails']['secureKey']
                );
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
            if (false !== ($orderIds = Tools::getOrderIdsByIdCart($data['cartDetails']['idCart']))) {
                foreach ($orderIds as $idOrder) {
                    $this->logger->debug(sprintf('Saving transaction for order %d', $idOrder));
                    /** @var \WorldlineOP\PrestaShop\Repository\TransactionRepository $transactionRepository */
                    $transactionRepository = $this->module->getService('worldlineop.repository.transaction');
                    $transaction = new WorldlineopTransaction();
                    $transaction->reference = pSQL($data['transaction']['merchantReference']);
                    $transaction->id_order = (int) $idOrder;
                    try {
                        $transactionRepository->save($transaction);
                    } catch (Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                    if ($data['sendMail']) {
                        $this->logger->debug('Sending mail');
                        try {
                            Tools::sendPendingCaptureMail($idOrder);
                        } catch (Exception $e) {
                            $this->logger->error($e->getMessage());
                        }
                    }
                }
            }
        } elseif ($data['updateOrderStatus']) {
            foreach ($data['order']['ids'] as $idOrder) {
                $order = new Order((int) $idOrder);
                if (!count($order->getHistory($this->context->language->id, $data['idOrderState']))) {
                    $orderHistory = new \OrderHistory();
                    $orderHistory->id_order = (int) $idOrder;
                    try {
                        $orderHistory->changeIdOrderState($data['idOrderState'], $idOrder);
                        $orderHistory->addWithemail();
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                    if (count($order->getOrderPayments()) > count($data['payments']['hasPayments'])) {
                        Db::getInstance()->update(
                            'order_payment',
                            ['transaction_id' => $data['payments']['merchantReference']],
                            'order_reference = "'.pSQL($order->reference).'"'
                        );
                    }
                    if ($data['sendMail']) {
                        $this->logger->debug('Sending mail');
                        Tools::sendPendingCaptureMail($order->id);
                    }
                }
            }
        } else {
            exit;
        }
        exit;
    }

    /**
     *
     */
    public function getRequest()
    {
        if (isset($_SERVER['X-GCS-Webhooks-Endpoint-Verification'])) {
            echo $_SERVER['X-GCS-Webhooks-Endpoint-Verification'];
        }
        header('HTTP/1.1 200 OK');
        exit;
    }
}
