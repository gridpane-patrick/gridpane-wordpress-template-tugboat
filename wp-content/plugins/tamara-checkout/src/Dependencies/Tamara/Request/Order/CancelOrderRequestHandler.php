<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order;

use Tamara\Wp\Plugin\Dependencies\Tamara\Request\AbstractRequestHandler;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Payment\CancelResponse;

class CancelOrderRequestHandler extends AbstractRequestHandler
{
    private const CANCEL_ORDER_ENDPOINT = '/orders/%s/cancel';

    public function __invoke(CancelOrderRequest $request)
    {
        $response = $this->httpClient->post(
            sprintf(self::CANCEL_ORDER_ENDPOINT, $request->getOrderId()),
            $request->toArray()
        );

        return new CancelResponse($response);
    }
}
