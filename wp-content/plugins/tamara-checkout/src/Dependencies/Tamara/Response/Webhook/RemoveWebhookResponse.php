<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook;

use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class RemoveWebhookResponse extends ClientResponse
{
    protected function parse(array $responseData): void
    {
    }
}