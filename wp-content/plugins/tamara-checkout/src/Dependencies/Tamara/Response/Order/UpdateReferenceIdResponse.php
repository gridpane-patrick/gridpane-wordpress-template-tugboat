<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order;

use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class UpdateReferenceIdResponse extends ClientResponse
{
    private const MESSAGE = 'message';

    /**
     * @var string
     */
    private $message;

    protected function parse(array $responseData): void
    {
        $this->message = $responseData[self::MESSAGE] ?? '';
    }
}
