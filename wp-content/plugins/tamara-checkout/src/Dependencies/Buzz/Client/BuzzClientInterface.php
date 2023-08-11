<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Client;

use Tamara\Wp\Plugin\Dependencies\Http\Client\HttpClient;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Client\ClientInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface BuzzClientInterface extends ClientInterface, HttpClient
{
    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface;
}
