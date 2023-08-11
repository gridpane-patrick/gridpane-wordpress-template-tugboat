<?php

namespace Tamara\Wp\Plugin\Dependencies\GuzzleHttp;

use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
