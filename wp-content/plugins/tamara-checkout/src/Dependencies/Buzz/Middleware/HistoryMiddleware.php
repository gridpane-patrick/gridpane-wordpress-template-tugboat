<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Middleware;

use Tamara\Wp\Plugin\Dependencies\Buzz\Middleware\History\Journal;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;
use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\ResponseInterface;

class HistoryMiddleware implements MiddlewareInterface
{
    private $journal;

    private $startTime;

    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }

    public function getJournal(): Journal
    {
        return $this->journal;
    }

    public function handleRequest(RequestInterface $request, callable $next)
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    public function handleResponse(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->journal->record($request, $response, microtime(true) - $this->startTime);

        return $next($request, $response);
    }
}
