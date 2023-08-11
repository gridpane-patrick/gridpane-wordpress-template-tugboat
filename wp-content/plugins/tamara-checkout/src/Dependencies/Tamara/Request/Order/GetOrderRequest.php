<?php

declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order;

class GetOrderRequest
{
    /**
     * @var string Tamara\Wp\Plugin\Dependencies\Tamara order id
     */
    private $orderId;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
