<?php

namespace Tamara\Wp\Plugin\Dependencies\Http\Client;

use Tamara\Wp\Plugin\Dependencies\Psr\Http\Client\ClientInterface;

/**
 * {@inheritdoc}
 *
 * Provide the Httplug HttpClient interface for BC.
 * You should typehint Tamara\Wp\Plugin\Dependencies\Psr\Http\Client\ClientInterface in new code
 */
interface HttpClient extends ClientInterface
{
}
