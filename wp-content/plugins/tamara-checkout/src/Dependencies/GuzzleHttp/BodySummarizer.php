<?php

namespace Tamara\Wp\Plugin\Dependencies\GuzzleHttp;

use Tamara\Wp\Plugin\Dependencies\Psr\Http\Message\MessageInterface;

final class BodySummarizer implements BodySummarizerInterface
{
    /**
     * @var int|null
     */
    private $truncateAt;

    public function __construct(int $truncateAt = null)
    {
        $this->truncateAt = $truncateAt;
    }

    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string
    {
        return $this->truncateAt === null
            ? \Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Psr7\Message::bodySummary($message)
            : \Tamara\Wp\Plugin\Dependencies\GuzzleHttp\Psr7\Message::bodySummary($message, $this->truncateAt);
    }
}
