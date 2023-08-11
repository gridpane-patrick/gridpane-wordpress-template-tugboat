<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Exception;

use Tamara\Wp\Plugin\Dependencies\Http\Client\Exception as HTTPlugException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ClientException extends \RuntimeException implements ExceptionInterface, HTTPlugException
{
}
