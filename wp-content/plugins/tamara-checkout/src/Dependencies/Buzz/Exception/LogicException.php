<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Exception;

/**
 * Thrown whenever a required call-flow is not respected.
 */
class LogicException extends \LogicException implements ExceptionInterface
{
}
