<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Request\AbstractRequestHandler;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\CreateCheckoutResponse;

class CreateCheckoutRequestHandler extends AbstractRequestHandler
{
    private const CHECKOUT_ENDPOINT = '/checkout';

    public function __invoke(CreateCheckoutRequest $request)
    {
        $response = $this->httpClient->post(
            self::CHECKOUT_ENDPOINT,
            $request->getOrder()->toArray()
        );

        return new CreateCheckoutResponse($response);
    }
}
