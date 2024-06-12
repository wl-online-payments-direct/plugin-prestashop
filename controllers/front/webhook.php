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
if (!defined('_PS_VERSION_')) {
    exit;
}

use OnlinePayments\Sdk\Webhooks\InMemorySecretKeyStore;
use OnlinePayments\Sdk\Webhooks\WebhooksHelper;
use WorldlineOP\PrestaShop\Configuration\Entity\Settings;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class WorldlineopWebhookModuleFrontController
 */
class WorldlineopWebhookModuleFrontController extends ModuleFrontController
{
    /** @var Worldlineop */
    public $module;

    /** @var \Monolog\Logger */
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
     *
     * @throws Exception
     */
    public function postRequest($data)
    {
        /** @var Settings $settings */
        $settings = $this->module->getService('worldlineop.settings');

        $secretKeyStore = new InMemorySecretKeyStore([$settings->credentials->webhooksKey => $settings->credentials->webhooksSecret]);
        $helper = new WebhooksHelper($secretKeyStore);
        try {
            $event = $helper->unmarshal($data, Tools::getServerHttpHeaders());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            header('HTTP/1.1 200 OK');
            exit;
        }
        $this->logger->debug('Webhook call', ['event' => json_decode($event->toJson(), true)]);
        //header('HTTP/1.1 200 OK');
        $this->respondOK();

        /** @var \WorldlineOP\PrestaShop\Presenter\WebhookEventPresenter $eventPresenter */
        $eventPresenter = $this->module->getService('worldlineop.event.presenter');
        try {
            $eventPresenter->handlePending($event, $settings);
            $presentedData = $eventPresenter->present($event, $this->context->shop->id);
            /** @var \WorldlineOP\PrestaShop\Processor\TransactionResponseProcessor $transactionResponseProcessor */
            $transactionResponseProcessor = $this->module->getService('worldlineop.processor.transaction');
            $transactionResponseProcessor->process($presentedData);
        } catch (Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                ['line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()]
            );
        }

        exit;
    }

    public function getRequest()
    {
        if (isset($_SERVER['X-GCS-Webhooks-Endpoint-Verification'])) {
            echo $_SERVER['X-GCS-Webhooks-Endpoint-Verification'];
        }
        header('HTTP/1.1 200 OK');
        exit;
    }

    /**
     * respondOK.
     */
    public function respondOK()
    {
        // check if fastcgi_finish_request is callable
        if (is_callable('fastcgi_finish_request')) {
            /*
             * This works in Nginx but the next approach not
             */
            session_write_close();
            fastcgi_finish_request();
            return;
        }
        ignore_user_abort(true);
        ob_start();
        header('HTTP/1.1 200 OK');
        header('Content-Encoding: none');
        header('Content-Length: '.ob_get_length());
        header('Connection: close');
        ob_end_flush();
        ob_flush();
        flush();
    }

}
