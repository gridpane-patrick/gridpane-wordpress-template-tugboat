<?php


namespace Tamara\Wp\Plugin\Traits;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentOptionsAvailability;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CheckPaymentOptionsAvailabilityRequest;
use Tamara\Wp\Plugin\Services\WCTamaraGateway;
use Tamara\Wp\Plugin\TamaraCheckout;

trait TamaraPaymentTypesTrait
{
    /**
     * Populate Pay By Later min limit
     */
    public function populatePayLaterMinLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_LATER]['min_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_LATER]['min_limit'] : null;
    }

    /**
     * Populate Pay By Later max limit
     */
    public function populatePayLaterMaxLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_LATER]['max_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_LATER]['max_limit'] : null;
    }

    /**
     * Populate Pay Now min limit
     */
    public function populatePayNowMinLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NOW]['min_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NOW]['min_limit'] : null;
    }

    /**
     * Populate Pay Now max limit
     */
    public function populatePayNowMaxLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NOW]['max_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NOW]['max_limit'] : null;
    }

    /**
     * Populate Pay By Instalments min limit
     */
    public function populateInstalmentsMinLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['min_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['min_limit'] : null;
    }

    /**
     * Populate Pay By Instalments max limit
     */
    public function populateInstalmentsMaxLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['max_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['max_limit'] : null;
    }

    /**
     * Populate Pay By X min limit by country code
     *
     * @param $instalmentPeriod
     * @param $countryCode
     *
     * @return mixed|null
     */
    public function populateInstalmentPayInXMinLimit($instalmentPeriod, $countryCode)
    {
        $supportedInstalments = $this->getAllSupportedPayInX($countryCode);
        $instalmentPayInXMinLimit = null;
        if (!empty($supportedInstalments)) {
            foreach ($supportedInstalments as $supportedInstalment) {
                if ($instalmentPeriod === $supportedInstalment['instalments']) {
                    $instalmentPayInXMinLimit = $supportedInstalment['min_limit']['amount'] ?? null;
                }
            }
        }

        return $instalmentPayInXMinLimit;
    }

    /**
     * Populate Pay By X max limit by country code
     *
     * @param $instalmentPeriod
     * @param $countryCode
     *
     * @return mixed|null
     */
    public function populateInstalmentPayInXMaxLimit($instalmentPeriod, $countryCode)
    {
        $supportedInstalments = $this->getAllSupportedPayInX($countryCode);
        $instalmentPayInXMaxLimit = null;
        if (!empty($supportedInstalments)) {
            foreach ($supportedInstalments as $supportedInstalment) {
                if ($instalmentPeriod === $supportedInstalment['instalments']) {
                    $instalmentPayInXMaxLimit = $supportedInstalment['max_limit']['amount'] ?? null;
                }
            }
        }

        return $instalmentPayInXMaxLimit;
    }

    /**
     * Populate Pay Next Month min limit
     */
    public function populatePayNextMonthMinLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH]['min_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH]['min_limit'] : null;
    }

    /**
     * Populate Pay Next Month max limit
     */
    public function populatePayNextMonthMaxLimit()
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return !empty($wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH]['max_limit']) ?
            $wcTamaraGateway->getPaymentTypes()[WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH]['max_limit'] : null;
    }

    /**
     * Populate instalment limits amount to use
     *
     * @return array
     */
    public function populateInstalmentLimitAmountsToCompare()
    {
        $payByInstalmentsMinAmount = $this->populateInstalmentsMinLimit() ?? null;
        $payByInstalmentsMaxAmount = $this->populateInstalmentsMaxLimit() ?? null;
        $minAmountOfEnabledPriorityInstalment = $this->getMinAmountOfEnabledPriorityInstalment() ?? null;
        $maxAmountOfEnabledPriorityInstalment = $this->getMaxAmountOfEnabledPriorityInstalment() ?? null;

        if ($this->isPayByInstalmentsEnabled()) {
            return [
                'instalmentMinAmount' => $payByInstalmentsMinAmount,
                'instalmentMaxAmount' => $payByInstalmentsMaxAmount,
            ];
        } else {
            return [
                'instalmentMinAmount' => $minAmountOfEnabledPriorityInstalment,
                'instalmentMaxAmount' => $maxAmountOfEnabledPriorityInstalment,
            ];
        }
    }

    /**
     * Return all enabled Pay In Xs and the limit amounts
     *
     * @return array
     */
    public function populateEnabledPayInXsAndLimitAmounts()
    {
        $countryCode = $this->getWCTamaraGatewayService()->getCurrentCountryCode();
        $enabledPayInXsAndLimitAmounts = [];
        for ($i = 12; $i >= 2; $i--) {
            $minAmount = $this->populateInstalmentPayInXMinLimit($i, $countryCode);
            $maxAmount = $this->populateInstalmentPayInXMaxLimit($i, $countryCode);
            if (!empty($minAmount && $maxAmount)) {
                if ($this->getWCTamaraGatewayService()->isSingleCheckoutEnabled()) {
                    $enabledPayInXsAndLimitAmounts[$i]['minAmount'] = $minAmount;
                    $enabledPayInXsAndLimitAmounts[$i]['maxAmount'] = $maxAmount;
                } else {
                    if ($this->isPayInXEnabled($i, $countryCode)) {
                        $enabledPayInXsAndLimitAmounts[$i]['minAmount'] = $minAmount;
                        $enabledPayInXsAndLimitAmounts[$i]['maxAmount'] = $maxAmount;
                    }
                }
            }
        }

        return $enabledPayInXsAndLimitAmounts;
    }

    /**
     * Populate Pay In X priority and its limit amount to use compared to product price
     *
     * @param $productPrice
     *
     * @return array
     */
    public function populatePayInXsLimitAmountBasedOnProductPrice($productPrice)
    {
        $payByInstalmentsMinAmount = $this->populateInstalmentsMinLimit() ?? null;
        $payByInstalmentsMaxAmount = $this->populateInstalmentsMaxLimit() ?? null;

        if ($this->isPayByInstalmentsEnabled()) {
            return [
                'instalment' => 3,
                'instalmentMinAmount' => $payByInstalmentsMinAmount,
                'instalmentMaxAmount' => $payByInstalmentsMaxAmount,
            ];
        } else {
            $displayedProductPrice = $productPrice ?? $this->getDisplayedProductPrice();
            $minAmountOfEnabledPriorityInstalment = '';
            $maxAmountOfEnabledPriorityInstalment = '';
            $instalment = '';
            $instalments = $this->populateEnabledPayInXsAndLimitAmounts() ?? [];

            if ($instalments) {
                foreach ($instalments as $i => $limit) {
                    if ($this->doesAmountMeetLimit($displayedProductPrice, $limit['maxAmount'], $limit['minAmount'])) {
                        $instalment = $i;
                        $minAmountOfEnabledPriorityInstalment = $limit['minAmount'];
                        $maxAmountOfEnabledPriorityInstalment = $limit['maxAmount'];
                        break;
                    }
                }
            }

            if (empty($instalment) && $this->isAlwaysShowWidgetPopupEnabled()) {
                foreach ($instalments as $i => $limit) {
                    // Return the highest instalment period when Always Show Widget Popup setting is enabled
                    $instalment = $i;
                    $minAmountOfEnabledPriorityInstalment = $limit['minAmount'];
                    $maxAmountOfEnabledPriorityInstalment = $limit['maxAmount'];
                    break;
                }
            }

            return [
                'instalment' => $instalment,
                'instalmentMinAmount' => $minAmountOfEnabledPriorityInstalment,
                'instalmentMaxAmount' => $maxAmountOfEnabledPriorityInstalment,
            ];
        }
    }

    /**
     * Check whether a country and its payment types are supported
     *
     * @param $countryCode
     *
     * @return bool
     */
    public function isSupportedCurrency($countryCode)
    {
        $countryPaymentTypes = $this->getWCTamaraGatewayService()->getCountryPaymentTypes() ?? [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryPaymentType => $paymentTypes) {
                if ($countryPaymentType === $countryCode) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check whether any payment type of a supported currency is enabled
     */
    public function isAnyPaymentTypeofASupportedCurrencyEnabled($countryCode)
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        for ($i = 12; $i >= 2; $i--) {
            if ($this->isTamaraGatewayEnabled()) {
                if (($wcTamaraGateway->isSingleCheckoutEnabled() && (!empty($this->getAllSupportedPayInX($countryCode))
                    || $this->isPayLaterSupported($countryCode) || $this->isPayNextMonthSupported($countryCode)))
                    || (!$wcTamaraGateway->isSingleCheckoutEnabled() && $this->isPayInXEnabled($i, $countryCode))
                ) {
                    return true;
                    break;
                }
            }
        }
    }

    /**
     * Get all supported Pay In X of a country
     */
    public function getAllSupportedPayInX($countryCode)
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();

        return $wcTamaraGateway->getPaymentTypes($countryCode)[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['payment_type_array']['supported_instalments'] ?? [];
    }

    /**
     * Check if Pay Later of a country is supported
     */
    public function isPayLaterSupported($countryCode)
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();
        $paymentTypeArr = $wcTamaraGateway->getPaymentTypes($countryCode)[WCTamaraGateway::PAYMENT_TYPE_PAY_BY_LATER]['payment_type_array'] ?? [];
        if (!empty($paymentTypeArr)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if Pay Next Month of a country is supported
     */
    public function isPayNextMonthSupported($countryCode)
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();
        $paymentTypeArr = $wcTamaraGateway->getPaymentTypes($countryCode)[WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH]['payment_type_array'] ?? [];
        if (!empty($paymentTypeArr)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $price
     * @param $currency
     * @param $language
     *
     * @return array
     */
    public function populatePDPWidgetBasedOnPrice($price, $currency = 'SAR', $language = '')
    {
        $dataPrice = $price ?? 0;
        $dataCurrency = $currency ?? get_woocommerce_currency();
        $dataLanguage = !empty($language) ? $language : substr(get_locale(), 0, 2);
        $wcTamaraGateway = $this->getWCTamaraGatewayService();
        $countryCodeByCurrency = $wcTamaraGateway->getCurrencyToCountryMapping()[$dataCurrency];
        $payLaterMinLimit = $this->populatePayLaterMinLimit();
        $payLaterMaxLimit = $this->populatePayLaterMaxLimit();
        $payNextMonthMinLimit = $this->populatePayNextMonthMinLimit();
        $payNextMonthMaxLimit = $this->populatePayNextMonthMaxLimit();
        $isPayByLaterEnabled = $this->isPayByLaterEnabled();
        $isPayLaterPDPEnabled = $this->isPayLaterPDPEnabled();
        $priorityInstalment = $this->populatePayInXsLimitAmountBasedOnProductPrice($dataPrice)['instalment'] ?? '';
        $disableInstalment = true;
        $disablePaylater = true;
        $dataPaymentType = '';
        $instalmentMinAmountToCompare = $this->populatePayInXsLimitAmountBasedOnProductPrice($dataPrice)['instalmentMinAmount'];
        $instalmentMaxAmountToCompare = $this->populatePayInXsLimitAmountBasedOnProductPrice($dataPrice)['instalmentMaxAmount'];
        $isAlwaysShowPopupWidgetEnabled = $this->isAlwaysShowWidgetPopupEnabled();
        $isPayNextMonthSupported = $this->isPayNextMonthSupported($countryCodeByCurrency);
        $isPayLaterSupported = $this->isPayLaterSupported($countryCodeByCurrency);
        $getAllSupportedPayInX = $this->getAllSupportedPayInX($countryCodeByCurrency);

        if ($payLaterMaxLimit <= static::PAY_LATER_PDP_MAX_AMOUNT) {
            $payByLaterMaxLimitToCompare = $payLaterMaxLimit;
        } else {
            $payByLaterMaxLimitToCompare = static::PAY_LATER_PDP_MAX_AMOUNT;
        }

        if ($wcTamaraGateway->isSingleCheckoutEnabled()) {
            $disablePaylater = true;
            $disableInstalment = false;
            $dataPaymentType = 'installment';
            $priorityInstalment = 4;
            $instalmentMinAmountToCompare = 0;
            $instalmentMaxAmountToCompare = 99999999999;
        } else {
            if (!$this->isSupportedCurrency($countryCodeByCurrency)
                || !$this->isAnyPaymentTypeofASupportedCurrencyEnabled($countryCodeByCurrency)) {
                $disablePaylater = true;
                $disableInstalment = true;
            } elseif ($isAlwaysShowPopupWidgetEnabled) {
                if (!empty($getAllSupportedPayInX && !empty($priorityInstalment))) {
                    $disablePaylater = true;
                    $disableInstalment = false;
                    $dataPaymentType = 'installment';
                } elseif (empty($getAllSupportedPayInX) && $isPayNextMonthSupported) {
                    $dataPaymentType = 'pay-next-month';
                    $disablePaylater = true;
                    $disableInstalment = true;
                    $priorityInstalment = null;
                } elseif (empty($getAllSupportedPayInX) && !$isPayNextMonthSupported && $isPayByLaterEnabled && $isPayLaterPDPEnabled && $isPayLaterSupported) {
                    $disableInstalment = true;
                    $disablePaylater = false;
                    $dataPaymentType = 'paylater';
                    $priorityInstalment = null;
                }
            } else {
                if (!empty($getAllSupportedPayInX) && !empty($priorityInstalment)
                    && $this->doesAmountMeetLimit($dataPrice, $instalmentMaxAmountToCompare, $instalmentMinAmountToCompare)) {
                    $disablePaylater = true;
                    $disableInstalment = false;
                    $dataPaymentType = 'installment';
                } elseif ($isPayNextMonthSupported && $this->doesAmountMeetLimit($dataPrice, $payNextMonthMaxLimit, $payNextMonthMinLimit)
                          && ($dataPrice > $instalmentMaxAmountToCompare || $dataPrice < $instalmentMinAmountToCompare)
                ) {
                    $dataPaymentType = 'pay-next-month';
                    $disablePaylater = true;
                    $disableInstalment = true;
                    $priorityInstalment = null;
                } elseif (($isPayNextMonthSupported && ($dataPrice > $payNextMonthMaxLimit || $dataPrice < $payNextMonthMinLimit)) && $isPayByLaterEnabled && $isPayLaterPDPEnabled
                          && $isPayLaterSupported && $this->doesAmountMeetLimit($dataPrice, $payLaterMaxLimit, $payLaterMinLimit)
                ) {
                    $disableInstalment = true;
                    $disablePaylater = false;
                    $dataPaymentType = 'paylater';
                    $priorityInstalment = null;
                }
            }
        }

        return [
            'price' => esc_attr($dataPrice),
            'lang' => esc_attr($dataLanguage),
            'paymentType' => esc_attr($dataPaymentType),
            'numberOfInstallments' => esc_attr($priorityInstalment),
            'currency' => esc_attr($dataCurrency),
            'payLaterMaxAmount' => esc_attr($payByLaterMaxLimitToCompare),
            'disablePaylater' => $disablePaylater ? 'true' : 'false',
            'disableInstallment' => $disableInstalment ? 'true' : 'false',
            'installmentMinimumAmount' => $isAlwaysShowPopupWidgetEnabled ? 0 : esc_attr($instalmentMinAmountToCompare),
            'installmentMaximumAmount' => $isAlwaysShowPopupWidgetEnabled ? 99999999999 : esc_attr($instalmentMaxAmountToCompare),
            'installmentAvailableAmount' => esc_attr($instalmentMinAmountToCompare),
            'countryCode' => esc_attr($countryCodeByCurrency),
        ];
    }

    /**
     * @param $amount
     * @param $maxAmount
     * @param $minAmount
     *
     * @return bool
     */
    public function doesAmountMeetLimit($amount, $maxAmount, $minAmount)
    {
        return !!($amount <= $maxAmount && $amount >= $minAmount);
    }

    /**
     * Get payment types of a country pulling directly from Tamara API Remote
     *
     * @param $countryCode
     * @param string $currency
     * @param string $phone
     * @param $total
     *
     * @return array
     */
    public function getPaymentTypesFromRemote($countryCode, $currency = '', $phone = '', $total = null)
    {
        $currency = !empty($currency) ? $currency : get_woocommerce_currency();
        $total = new Money($total, $currency);
        $countryPaymentTypes = $this->getWCTamaraGatewayService()->getCountryPaymentTypes(false, $currency, $phone, $total) ?? [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryPaymentType => $paymentTypes) {
                if ($countryPaymentType === $countryCode) {
                    return $countryPaymentTypes[$countryCode];
                }
            }
        } else {
            return $countryPaymentTypes;
        }
    }

    /**
     * @param $cartTotal
     * @param $customerPhone
     * @param $countryCode
     * @param false $isVip
     * @param bool $getFromCache
     *
     * @return mixed
     * @throws \Tamara\Wp\Plugin\Dependencies\Tamara\Exception\RequestDispatcherException
     */
    public function getAvailablePaymentOptions($cartTotal, $customerPhone, $countryCode, $isVip = false, $getFromCache = true)
    {
        /** @var WCTamaraGateway $wcTamaraGateway */
        $wcTamaraGateway = $this->getWCTamaraGatewayService();
        $countryPaymentTypesCacheKeyV2 = $wcTamaraGateway->buildCountryPaymentTypesCacheKeyV2($cartTotal, $customerPhone, $countryCode, $isVip);
        $availablePaymentOptions = [];
        if ($getFromCache) {
            $availablePaymentOptions = get_transient($countryPaymentTypesCacheKeyV2);
            if (empty($availablePaymentOptions) && $this->isSupportedCountry($countryCode)) {
                $currencyByCountryCode = array_flip($wcTamaraGateway->getCurrencyToCountryMapping());
                $currency = $currencyByCountryCode[$countryCode];
                $paymentOptionsAvailability = new PaymentOptionsAvailability();
                $paymentOptionsAvailability->setOrderValue(new Money($cartTotal, $currency));
                $paymentOptionsAvailability->setPhoneNumber($customerPhone);
                $paymentOptionsAvailability->setCountry($countryCode);
                $paymentOptionsAvailability->setIsVip($isVip);
                $client = $wcTamaraGateway->tamaraClient;
                $checkPaymentOptionsAvailabilityRequest = new CheckPaymentOptionsAvailabilityRequest($paymentOptionsAvailability);
                try {
                    $checkPaymentOptionsAvailabilityResponse = $client->checkPaymentOptionsAvailability($checkPaymentOptionsAvailabilityRequest);
                } catch (Exception $exception) {
                    $this->logMessage(
                        sprintf(
                            "Cannot proceed Tamara Checkout Payment Options Availibility.\nError message: ' %s'.\nTrace: %s",
                            $exception->getMessage(),
                            $exception->getTraceAsString()
                        )
                    );
                    throw new Exception('Cannot proceed Tamara Checkout Payment Options Availibility');
                }

                if ($checkPaymentOptionsAvailabilityResponse->isSuccess()) {
                    $availablePaymentOptions['hasAvailablePaymentOptions'] = $checkPaymentOptionsAvailabilityResponse->hasAvailablePaymentOptions() ?? false;
                    $availablePaymentOptions['isSingleCheckoutEnabled'] = $checkPaymentOptionsAvailabilityResponse->isSingleCheckoutEnabled() ?? false;
                    $availablePaymentOptions['getAvailablePaymentLabels'] = $checkPaymentOptionsAvailabilityResponse->getAvailablePaymentLabels()->getIterator() ?? [];
                    set_transient($countryPaymentTypesCacheKeyV2, $availablePaymentOptions, 600);
                } else {
                    $errors = $checkPaymentOptionsAvailabilityResponse->getMessage();
                    $this->logMessage(
                        sprintf("Tamara Checkout Payment Options Availibility Check Failed.\nError message: ' %s'", $errors)
                    );

                    return false;
                }
            }
        }

        return $availablePaymentOptions;
    }

    /**
     * @param $cartTotal
     * @param $customerPhone
     * @param $countryCode
     * @param false $isVip
     *
     * @return bool
     * @throws \Tamara\Exception\RequestDispatcherException
     */
    public function hasAvailablePaymentOptions($cartTotal, $customerPhone, $countryCode, $isVip = false)
    {
        $availablePaymentOptions = $this->getAvailablePaymentOptions($cartTotal, $customerPhone, $countryCode) ?? [];
        if (!empty($availablePaymentOptions)) {
            return !!$availablePaymentOptions['hasAvailablePaymentOptions'];
        }
    }

    /**
     * @param $cartTotal
     * @param $customerPhone
     * @param $countryCode
     * @param false $isVip
     *
     * @return bool
     * @throws \Tamara\Exception\RequestDispatcherException
     */
    public function isSingleCheckoutEnabled($cartTotal, $customerPhone, $countryCode, $isVip = false)
    {
        $availablePaymentOptions = $this->getAvailablePaymentOptions($cartTotal, $customerPhone, $countryCode) ?? [];
        if (!empty($availablePaymentOptions)) {
            return !!$availablePaymentOptions['isSingleCheckoutEnabled'];
        }
    }

    /**
     * @param $cartTotal
     * @param $customerPhone
     * @param $countryCode
     * @param false $isVip
     *
     * @return array
     * @throws \Tamara\Exception\RequestDispatcherException
     */
    public function getPaymentOptions($cartTotal, $customerPhone, $countryCode, $isVip = false)
    {
        $availablePaymentOptions = $this->getAvailablePaymentOptions($cartTotal, $customerPhone, $countryCode) ?? [];
        $paymentOptions = [];
        if (!empty($availablePaymentOptions)) {
            $paymentOptions = $availablePaymentOptions['getAvailablePaymentLabels'] ?? [];
        }

        return $paymentOptions;
    }
}