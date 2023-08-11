<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook;

use Tamara\Wp\Plugin\Dependencies\Tamara\Request\AbstractRequestHandler;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook\RegisterWebhookResponse;

class RegisterWebhookRequestHandler extends AbstractRequestHandler
{
    private const REGISTER_WEBHOOK_ENDPOINT = '/webhooks';

    public function __invoke(RegisterWebhookRequest $request)
    {
        $response = $this->httpClient->post(
            self::REGISTER_WEBHOOK_ENDPOINT,
            $request->toArray()
        );

        return new RegisterWebhookResponse($response);
    }
}