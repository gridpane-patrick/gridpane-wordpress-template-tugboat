<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Exception;

use Tamara\Wp\Plugin\Dependencies\Psr\Http\Client\RequestExceptionInterface as PsrRequestException;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RequestException extends ClientException implements PsrRequestException
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
