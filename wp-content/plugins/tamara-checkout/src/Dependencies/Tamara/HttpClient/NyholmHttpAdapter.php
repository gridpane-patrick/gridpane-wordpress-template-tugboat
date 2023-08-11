<?php

namespace Tamara\Wp\Plugin\Dependencies\Tamara\HttpClient;

use Tamara\Wp\Plugin\Dependencies\Buzz\Client\Curl;
use Tamara\Wp\Plugin\Dependencies\Nyholm\Psr7\Factory\Psr17Factory;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\ResponseInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Log\LoggerInterface;
use Tamara\Wp\Plugin\Dependencies\Tamara\Exception\RequestException;

class NyholmHttpAdapter implements ClientInterface
{
    private $client;

    /**
     * @var int
     */
    protected $requestTimeout;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(int $requestTimeout, LoggerInterface $logger = null)
    {
        $this->requestTimeout = $requestTimeout;
        $this->logger = $logger;
        $this->client = new Curl(new Psr17Factory());
    }

    /** {@inheritDoc} */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ): RequestInterface {
        return new \Tamara\Wp\Plugin\Dependencies\Nyholm\Psr7\Request(
            $method,
            $uri,
            $headers,
            $body
        );
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest(
                $request,
                [
                    'timeout' => $this->requestTimeout,
                ]
            );
        } catch (\Throwable $exception) {
            if (null !== $this->logger) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
            }

            throw new RequestException(
                $exception->getMessage(),
                $exception->getCode(),
                $request,
                null,
                $exception->getPrevious()
            );
        }
    }
}