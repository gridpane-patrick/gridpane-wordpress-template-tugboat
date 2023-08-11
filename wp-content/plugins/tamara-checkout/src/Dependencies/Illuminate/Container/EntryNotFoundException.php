<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Container;

use Exception;
use Tamara\Wp\Plugin\Dependencies\Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
