<?php

declare (strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentOptionsAvailability;

class CheckPaymentOptionsAvailabilityRequest
{

    private $paymentOptionsAvailability;

    public function __construct(PaymentOptionsAvailability $paymentOptionsAvailability)
    {
        $this->paymentOptionsAvailability = $paymentOptionsAvailability;
    }

    public function getPaymentOptionAvailability() : PaymentOptionsAvailability
    {
        return $this->paymentOptionsAvailability;
    }
}
