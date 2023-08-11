<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Merchant;

use Tamara\Wp\Plugin\Dependencies\Tamara\Request\AbstractRequestHandler;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Merchant\GetDetailsInfoResponse;

class GetDetailsInfoRequestHandler extends AbstractRequestHandler
{
    private const MERCHANT_CONFIGS_ENDPOINT = '/merchants/configs';

    public function __invoke(GetDetailsInfoRequest $request)
    {
        $response = $this->httpClient->get(
            self::MERCHANT_CONFIGS_ENDPOINT,
            []
        );

        return new GetDetailsInfoResponse($response);
    }
}
