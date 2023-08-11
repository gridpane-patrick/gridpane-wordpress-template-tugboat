<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\Dependencies\Psr\Log\LoggerInterface;
use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Client;
use function Tamara\Wp\Plugin\Dependencies\GuzzleHttp\choose_handler;

class GuzzleHttpAdapter extends \Tamara\HttpClient\GuzzleHttpAdapter
{
    /**
     * @param int $requestTimeout
     * @param LoggerInterface|null $logger
     */
    public function __construct(int $requestTimeout, LoggerInterface $logger = null)
    {
        $this->client = new Client([
            'handler' => choose_handler()
        ]);
        $this->requestTimeout = $requestTimeout;
        $this->logger = $logger;
    }
}
