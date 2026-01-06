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

use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\MobilePaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\PaymentProduct130SpecificInput;
use OnlinePayments\Sdk\Domain\PaymentProduct130SpecificThreeDSecure;
use OnlinePayments\Sdk\Domain\RedirectionData;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use WorldlineOP\PrestaShop\Utils\Tools;

/**
 * Class PaymentRequestBuilder
 */
class PaymentRequestBuilder extends AbstractRequestBuilder
{
    const TRANSACTION_RISK_ANALYSIS_EXEMPTION = 'transaction-risk-analysis';
    const LOW_VALUE_EXEMPTION = 'low-value';

    const NO_CHALLENGE_REQUESTED_RISK_ANALYSIS_PERFORMED = 'no-challenge-requested-risk-analysis-performed';
    const NO_CHALLENGE_REQUESTED = 'no-challenge-requested';

    /**
     * @return CardPaymentMethodSpecificInput
     *
     * @throws \Exception
     */
    public function buildCardPaymentMethodSpecificInput()
    {
        $cardPaymentMethodSpecificInput = new CardPaymentMethodSpecificInput();
        $cardPaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );
        if (false !== $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setToken($this->tokenValue);
        }
        if (false === $this->tokenValue) {
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor(self::CARD_ON_FILE_REQUESTOR_FIRST);
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator(self::CARD_ON_FILE_SEQUENCE_INDICATOR_FIRST);
        } else {
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor(self::CARD_ON_FILE_REQUESTOR_SUBSEQUENT);
            $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator(self::CARD_ON_FILE_SEQUENCE_INDICATOR_SUBSEQUENT);
        }

        $threeDSecure = new ThreeDSecure();
        if (self::PRODUCT_ID_MAESTRO == $this->idProduct || true === $this->settings->advancedSettings->force3DsV2) {
            $threeDSecure->setSkipAuthentication(false);

            $redirectionData = new RedirectionData();
            $redirectionData->setReturnUrl(
                $this->context->link->getModuleLink($this->module->name, 'redirect', ['action' => 'redirectReturnIframe'])
            );
            $threeDSecure->setRedirectionData($redirectionData);
        } else {
            $threeDSecure->setSkipAuthentication(true);
        }

        $orderTotalInEuros = Tools::getAmountInEuros($this->context->cart->getOrderTotal(), new \Currency($this->context->cart->id_currency));
        if (true === $this->settings->advancedSettings->threeDSExempted &&
            $this->settings->advancedSettings->threeDSExemptedValue >= $orderTotalInEuros) {
            $threeDSecure->setExemptionRequest($this->settings->advancedSettings->threeDSExemptedType);
            $threeDSecure->setSkipAuthentication(true);
            $threeDSecure->setSkipSoftDecline(false);
        }
        if (true === $this->settings->advancedSettings->enforce3DS) {
            $threeDSecure->setChallengeIndicator(self::CHALLENGE_INDICATOR_REQUIRED);
        }
        $cardPaymentMethodSpecificInput->setThreeDSecure($threeDSecure);

        if (true === $this->settings->advancedSettings->force3DsV2) {
            $paymentProduct130SpecificInput = new PaymentProduct130SpecificInput();
            $paymentProduct130ThreeDSecure = new PaymentProduct130SpecificThreeDSecure();
            $shoppingCartPresented = $this->shoppingCartPresenter->present($this->context->cart);
            $numberOfItems = min(count($shoppingCartPresented['products']), self::MAX_NUMBER_OF_ITEMS);

            $paymentProduct130ThreeDSecure->setUsecase('single-amount');
            $paymentProduct130ThreeDSecure->setNumberOfItems($numberOfItems);

            if (!$this->settings->advancedSettings->threeDSExempted) {
                $paymentProduct130ThreeDSecure->setAcquirerExemption(false);
            } elseif ($this->settings->advancedSettings->threeDSExempted) {
                if ($this->settings->advancedSettings->threeDSExemptedValue >= $orderTotalInEuros) {

                    $threeDSExemptedType = $this->settings->advancedSettings->threeDSExemptedType;
                    $paymentProduct130ThreeDSecure->setAcquirerExemption(
                        $threeDSExemptedType === self::TRANSACTION_RISK_ANALYSIS_EXEMPTION
                    );

                    $threeDSecure->setSkipAuthentication(false);
                    $threeDSecure->setExemptionRequest($threeDSExemptedType);
                    $threeDSecure->setSkipSoftDecline(false);

                    switch ($threeDSExemptedType) {
                        case self::TRANSACTION_RISK_ANALYSIS_EXEMPTION:
                            $threeDSecure->setChallengeIndicator(self::NO_CHALLENGE_REQUESTED_RISK_ANALYSIS_PERFORMED);
                            break;
                        case self::LOW_VALUE_EXEMPTION:
                            $threeDSecure->setChallengeIndicator(self::NO_CHALLENGE_REQUESTED);
                            break;
                    }
                } else {
                    $paymentProduct130ThreeDSecure->setAcquirerExemption(false);
                }
            }

            $paymentProduct130SpecificInput->setThreeDSecure($paymentProduct130ThreeDSecure);
            $cardPaymentMethodSpecificInput->setPaymentProduct130SpecificInput($paymentProduct130SpecificInput);
        }

        return $cardPaymentMethodSpecificInput;
    }

    /**
     * @return MobilePaymentMethodSpecificInput
     */
    public function buildMobilePaymentMethodSpecificInput()
    {
        $mobilePaymentMethodSpecificInput = new MobilePaymentMethodSpecificInput();
        $mobilePaymentMethodSpecificInput->setAuthorizationMode(
            $this->settings->advancedSettings->paymentSettings->transactionType
        );

        return $mobilePaymentMethodSpecificInput;
    }

    /**
     * @return null
     */
    public function buildHostedCheckoutSpecificInput()
    {
        return null;
    }
}
