<?php

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Request\AbstractRequestHandler;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\GetPaymentTypesResponse;

class GetPaymentTypesV2RequestHandler extends AbstractRequestHandler
{
    private const GET_PAYMENT_TYPES_V2_ENDPOINT = '/checkout/credit-pre-check';

    public function __invoke(GetPaymentTypesV2Request $request): GetPaymentTypesResponse
    {
        $response = $this->httpClient->post(
            self::GET_PAYMENT_TYPES_V2_ENDPOINT,
            $request->toArray()
        );

        return new GetPaymentTypesResponse($response);
    }
}
