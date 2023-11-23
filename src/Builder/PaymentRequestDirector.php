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

namespace WorldlineOP\PrestaShop\Builder;

use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;

/**
 * Class PaymentRequestDirector
 */
class PaymentRequestDirector
{
    /** @var AbstractRequestBuilder */
    private $builder;

    /**
     * @param AbstractRequestBuilder $builder
     */
    public function setBuilder(AbstractRequestBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param string|false $productId
     * @param string|false $tokenId
     *
     * @return CreateHostedCheckoutRequest
     *
     * @throws \Exception
     */
    public function buildHostedPaymentRequest($productId, $tokenId)
    {
        $this->builder->setData($productId, $tokenId, false);

        $hostedCheckoutRequest = new CreateHostedCheckoutRequest();
        $hostedCheckoutRequest->setHostedCheckoutSpecificInput($this->builder->buildHostedCheckoutSpecificInput());

        $cardPaymentMethodSpecificInput = $this->builder->buildCardPaymentMethodSpecificInput();
        $redirectPaymentMethodSpecificInput = $this->builder->buildRedirectPaymentMethodSpecificInput();
        $mobilePaymentMethodSpecificInput = $this->builder->buildMobilePaymentMethodSpecificInput();

        if (false !== $cardPaymentMethodSpecificInput) {
            $hostedCheckoutRequest->setCardPaymentMethodSpecificInput(
                $cardPaymentMethodSpecificInput
            );
        }
        if (false !== $mobilePaymentMethodSpecificInput) {
            $hostedCheckoutRequest->setMobilePaymentMethodSpecificInput(
                $this->builder->buildMobilePaymentMethodSpecificInput()
            );
        }
        if (false !== $redirectPaymentMethodSpecificInput) {
            $hostedCheckoutRequest->setRedirectPaymentMethodSpecificInput(
                $this->builder->buildRedirectPaymentMethodSpecificInput()
            );
        }
        $hostedCheckoutRequest->setOrder($this->builder->buildOrder());

        return $hostedCheckoutRequest;
    }

    /**
     * @param string|false $tokenId
     * @param array|false $ccForm
     *
     * @return CreatePaymentRequest
     *
     * @throws \Exception
     */
    public function buildPaymentRequest($tokenId, $ccForm)
    {
        $this->builder->setData(false, $tokenId, $ccForm);

        $paymentRequest = new CreatePaymentRequest();
        $paymentRequest->setCardPaymentMethodSpecificInput(
            $this->builder->buildCardPaymentMethodSpecificInput()
        );
        $paymentRequest->setOrder($this->builder->buildOrder());

        return $paymentRequest;
    }
}
