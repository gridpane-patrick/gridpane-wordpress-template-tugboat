<?php

namespace Tamara\Wp\Plugin\Dependencies\Http\Client\Exception;

use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\RequestInterface;

trait RequestAwareTrait
{
    /**
     * @var RequestInterface
     */
    private $request;

    private function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
