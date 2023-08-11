<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request;

use Tamara\Wp\Plugin\Dependencies\Tamara\HttpClient\HttpClient;

abstract class AbstractRequestHandler
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
