<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentTypeCollectionV2;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class CheckPaymentOptionsAvailabilityResponse extends ClientResponse
{

    public const HAS_AVAILABLE_PAYMENT_OPTIONS = 'has_available_payment_options',
        SINGLE_CHECKOUT_ENABLED = 'single_checkout_enabled',
        AVAILABLE_PAYMENT_LABELS = 'available_payment_labels';

    private $hasAvailablePaymentOptions;
    private $singleCheckoutEnabled;
    private $availablePaymentLabels;

    /**
     * @return bool
     */
    public function hasAvailablePaymentOptions() : bool
    {
        return boolval($this->hasAvailablePaymentOptions);
    }

    /**
     * @return bool
     */
    public function isSingleCheckoutEnabled() : bool
    {
        return boolval($this->singleCheckoutEnabled);
    }

    /**
     * @return mixed
     */
    public function getAvailablePaymentLabels()
    {
        return $this->availablePaymentLabels;
    }

    protected function parse(array $responseData) : void
    {
        $this->hasAvailablePaymentOptions = $responseData[self::HAS_AVAILABLE_PAYMENT_OPTIONS];
        $this->singleCheckoutEnabled      = $responseData[self::SINGLE_CHECKOUT_ENABLED];
        $this->availablePaymentLabels     = new PaymentTypeCollectionV2($responseData[self::AVAILABLE_PAYMENT_LABELS]);
    }
}
