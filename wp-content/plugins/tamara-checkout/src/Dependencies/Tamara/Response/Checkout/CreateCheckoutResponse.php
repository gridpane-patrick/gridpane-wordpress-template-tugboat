<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\CheckoutResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class CreateCheckoutResponse extends ClientResponse
{
    /**
     * @var CheckoutResponse|null
     */
    private $checkoutResponse;

    public function getCheckoutResponse(): ?CheckoutResponse
    {
        return $this->checkoutResponse;
    }

    protected function parse(array $responseData): void
    {
        $this->checkoutResponse = new CheckoutResponse($responseData);
    }
}
