<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Buzz\Message;

/**
 * Convert between Tamara\Wp\Plugin\Dependencies\Buzz style:
 * array(
 *   'foo: bar',
 *   'baz: biz',
 * ).
 *
 * and PSR style:
 * array(
 *   'foo' => 'bar'
 *   'baz' => ['biz', 'buz'],
 * )
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class HeaderConverter
{
    /**
     * Convert from PSR style headers to Tamara\Wp\Plugin\Dependencies\Buzz style.
     */
    public static function toBuzzHeaders(array $headers): array
    {
        $buzz = [];

        foreach ($headers as $key => $values) {
            if (!\is_array($values)) {
                $buzz[] = sprintf('%s: %s', $key, $values);
            } else {
                foreach ($values as $value) {
                    $buzz[] = sprintf('%s: %s', $key, $value);
                }
            }
        }

        return $buzz;
    }

    /**
     * Convert from Tamara\Wp\Plugin\Dependencies\Buzz style headers to PSR style.
     */
    public static function toPsrHeaders(array $headers): array
    {
        $psr = [];
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $psr[trim($key)][] = trim($value);
        }

        return $psr;
    }
}
