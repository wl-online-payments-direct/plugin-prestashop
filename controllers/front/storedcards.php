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

/**
 * Class WorldlineopStoredCardsModuleFrontController
 */
class WorldlineopStoredCardsModuleFrontController extends ModuleFrontController
{
    /** @var Worldlineop */
    public $module;

    /** @var bool */
    protected $redirectStoredCards = false;

    /** @var \Monolog\Logger */
    private $logger;

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        /** @var \WorldlineOP\PrestaShop\Logger\LoggerFactory $loggerFactory */
        $loggerFactory = $this->module->getService('worldlineop.logger.factory');
        $this->logger = $loggerFactory->setChannel('StoredCards');
        if ($this->redirectStoredCards) {
            $this->redirectWithNotifications($this->context->link->getModuleLink('worldlineop', 'storedcards', []));
        }
        parent::initContent();
        /** @var \WorldlineOP\PrestaShop\Presenter\StoredCardsPresenter $storedCardsPresenter */
        $storedCardsPresenter = $this->module->getService('worldlineop.storedcards.presenter');
        $this->context->smarty->assign([
            'stored_cards' => $storedCardsPresenter->present(),
        ]);

        $this->setTemplate('module:worldlineop/views/templates/front/storedcards.tpl');
    }

    /**
     * @return bool|void
     */
    public function setMedia()
    {
        parent::setMedia();

        return $this->registerStylesheet(
            'worldlineop-storedcards',
            $this->module->getPathUri() . 'views/css/storedcards.css',
            ['server' => 'remote']
        );
    }

    public function postProcess()
    {
        if (Tools::getValue('delete')) {
            $this->deleteCard();
        }

        parent::postProcess();
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function deleteCard()
    {
        $idStoreCard = (int) Tools::getValue('id_token');
        $tokenSent = Tools::getValue('token');
        $tokenCalculated = Tools::getToken(true, $this->context);
        if (!$idStoreCard || !$tokenSent || $tokenSent != $tokenCalculated) {
            $this->errors[] = $this->module->l('Could not delete stored card.', 'storedcards');

            return false;
        }
        /** @var \WorldlineOP\PrestaShop\Repository\TokenRepository $tokenRepository */
        $tokenRepository = $this->module->getService('worldlineop.repository.token');
        $storedCard = $tokenRepository->findById((int) $idStoreCard);
        if (false === $storedCard
            || $storedCard->id_customer != $this->context->customer->id
            || $storedCard->id_shop != $this->context->shop->id
        ) {
            $this->errors[] = $this->module->l('Could not delete stored card.', 'storedcards');

            return false;
        }

        /** @var \OnlinePayments\Sdk\Merchant\MerchantClient $merchantClient */
        $merchantClient = $this->module->getService('worldlineop.sdk.client');
        try {
            $merchantClient->tokens()->deleteToken($storedCard->value);
            $delete = $tokenRepository->delete($storedCard);
        } catch (Exception $e) {
            $this->module->logger->error($e->getMessage());
            $this->errors[] = $this->module->l('Could not delete stored card.', 'storedcards');

            return false;
        }
        if ($delete) {
            $this->success[] = $this->module->l('Card deleted successfully.', 'storedcards');
            $this->redirectStoredCards = true;

            return true;
        } else {
            $this->errors[] = $this->module->l('Could not delete stored card.', 'storedcards');

            return false;
        }
    }
}
