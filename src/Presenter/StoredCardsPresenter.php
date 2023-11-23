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

use Configuration;
use Context;
use Country;
use Currency;
use OnlinePayments\Sdk\Merchant\MerchantClient;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductParams;
use Worldlineop;
use WorldlineOP\PrestaShop\Repository\TokenRepository;

/**
 * Class StoredCardsPresenter
 */
class StoredCardsPresenter implements PresenterInterface
{
    /** @var Worldlineop */
    private $module;

    /** @var Context */
    private $context;

    /** @var MerchantClient */
    private $merchantClient;

    /** @var TokenRepository */
    private $tokenRepository;

    /**
     * StoredCardsPresenter constructor.
     *
     * @param Worldlineop $module
     * @param Context $context
     * @param MerchantClient $merchantClient
     * @param TokenRepository $tokenRepository
     */
    public function __construct(
        Worldlineop $module,
        Context $context,
        MerchantClient $merchantClient,
        TokenRepository $tokenRepository
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->merchantClient = $merchantClient;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    public function present()
    {
        $data = [
            'tokens' => [],
            'img_path' => $this->module->getPathUri() . 'views/img/',
        ];
        /** @var \WorldlineopToken[] $tokens */
        $tokens = $this->tokenRepository->findByIdCustomerIdShop(
            $this->context->customer->id,
            $this->context->customer->secure_key,
            $this->context->shop->id
        );
        if ($tokens) {
            foreach ($tokens as $token) {
                $query = new GetPaymentProductParams();
                $defaultCurrency = Currency::getDefaultCurrency();
                $query->setCurrencyCode($defaultCurrency instanceof Currency ? $defaultCurrency->iso_code : 'EUR');
                $query->setCountryCode(Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')));
                try {
                    $productDetails = $this->merchantClient->products()->getPaymentProduct($token->product_id, $query);
                } catch (\Exception $e) {
                    continue;
                }
                $logoPath = realpath(
                    $this->module->getLocalPath() . sprintf('views/img/payment_logos/%s.svg', $token->product_id)
                );
                $logoUrl = false;
                if (false !== $logoPath) {
                    $logoUrl = $this->module->getPathUri() . sprintf('views/img/payment_logos/%s.svg', $token->product_id);
                }

                $data['tokens'][] = [
                    'id' => $token->id,
                    'card_number' => chunk_split($token->card_number, 4, ' '),
                    'expiry_date' => \Tools::substr($token->expiry_date, 0, 2) . '/' . \Tools::substr($token->expiry_date, 2, 2),
                    'card_brand' => $productDetails->getDisplayHints()->getLabel(),
                    'logo_url' => $logoUrl,
                ];
            }
        }

        return $data;
    }
}
