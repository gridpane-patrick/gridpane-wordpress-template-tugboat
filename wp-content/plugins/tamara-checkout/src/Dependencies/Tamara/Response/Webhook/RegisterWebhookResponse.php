<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Webhook;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class RegisterWebhookResponse extends ClientResponse
{
    /**
     * @var string
     */
    private $webhookId;

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    protected function parse(array $responseData): void
    {
        $this->webhookId = $responseData[Webhook::WEBHOOK_ID];
    }
}