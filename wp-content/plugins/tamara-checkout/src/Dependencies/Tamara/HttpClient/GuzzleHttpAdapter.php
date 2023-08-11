<?php

namespace Tamara\Wp\Plugin\Dependencies\Tamara\HttpClient;

use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Client;
use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\ClientInterface as GuzzleHttpClient;
use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Exception\GuzzleException;
use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Psr7\Request;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\ResponseInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Log\LoggerInterface;
use Tamara\Wp\Plugin\Dependencies\Tamara\Exception\RequestException;
use Throwable;

class GuzzleHttpAdapter implements ClientInterface
{
    /**
     * @var GuzzleHttpClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $requestTimeout;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param int $requestTimeout
     * @param LoggerInterface|null $logger
     */
    public function __construct(int $requestTimeout, LoggerInterface $logger = null)
    {
        $this->client = new Client();
        $this->requestTimeout = $requestTimeout;
        $this->logger = $logger;
    }

    /** {@inheritDoc} */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ): RequestInterface {
        return new Request(
            $method,
            $uri,
            $headers,
            $body
        );
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws RequestException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->send(
                $request,
                [
                    'timeout' => $this->requestTimeout,
                ]
            );
        } catch (Throwable | GuzzleException | GuzzleRequestException $exception) {
            if (null !== $this->logger) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
            }

            $exceptionResponse = null;
            if (method_exists($exception, 'getResponse')) {
                $exceptionResponse = $exception->getResponse();
            }

            throw new RequestException(
                $exception->getMessage(),
                $exception->getCode(),
                $request,
                $exceptionResponse,
                $exception->getPrevious()
            );
        }
    }
}
